<?php

namespace Feeder\Command;

use Elasticsearch\ClientBuilder;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class ElasticsearchCommand
 *
 * @package Feeder\Command
 * @author  Michael BOUVY <michael.bouvy@clickandmortar.fr>
 */
class ElasticsearchCommand extends Command
{
    /**
     * Default since value
     *
     * @var string
     */
    const DEFAULT_SINCE = '24 hours ago';

    /**
     * @var bool Debug mode
     */
    protected $debug = false;

    /**
     * @var ClientBuilder
     */
    protected $esClient;

    /**
     * @var array
     */
    protected $esHosts = ['http://localhost:9200'];

    /**
     * @var PDO Connection to database
     */
    protected $connection = null;

    /**
     * @var array Timers
     */
    protected $timers = [];

    /**
     * @return array
     */
    public function getEsHosts()
    {
        return $this->esHosts;
    }

    /**
     * @param array $esHosts
     */
    public function setEsHosts($esHosts)
    {
        if (!is_array($esHosts)) {
            $esHosts = array($esHosts);
        }

        $this->esHosts = $esHosts;
    }

    /**
     * @param PDO $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addOption('debug', 'd', InputOption::VALUE_OPTIONAL, 'Debug mode')
            ->addOption('since', 's', InputOption::VALUE_OPTIONAL, 'Import data since', self::DEFAULT_SINCE);

        date_default_timezone_set('UTC');
    }

    /**
     * Execute
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $debug = $input->getOption('debug');
        if ($debug) {
            $this->setDebug(true);
        }

        $scriptDir = realpath(dirname($_SERVER['SCRIPT_FILENAME']));

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8',
            getenv('DB_HOST'),
            getenv('DB_PORT'),
            getenv('DB_NAME')
        );
        $db = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->setConnection($db);

        $esHost = getenv('ES_HOST');
        if (!empty($esHost)) {
            $this->setEsHosts($esHost);
        }
    }

    /**
     * Check debug mode
     *
     * @return bool
     */
    protected function isDebug()
    {
        return $this->debug;
    }

    /**
     * Set debug mode
     *
     * @param bool $value Debug status
     *
     * @return void
     */
    protected function setDebug($value)
    {
        $this->debug = $value;
    }

    /**
     * Initialize Elasticsearch:
     * - Create index
     * - Put mapping
     *
     * @param string $indexName Index name
     * @param string $typeName  Type name
     * @param array  $fields    Fields
     *
     * @return void
     */
    protected function initializeElasticsearch($indexName, $typeName, $fields)
    {
        $client = $this->getElasticsearchClient();

        $params = [
            'index' => $indexName,
            'body'  => [
                'settings' => [
                    'number_of_shards'   => 4,
                    'number_of_replicas' => 0
                ]
            ]
        ];

        if (!$client->indices()->exists(array('index' => $indexName))) {
            $client->indices()->create($params);
        }

        $mapping = array(
            'index' => $indexName,
            'type'  => $typeName,
            'body'  => array()
        );

        foreach ($fields as $fieldName => $fieldData) {
            $type = $fieldData['type'];
            $format = isset($fieldData['format']) ? $fieldData['format'] : '';

            $property = array("type" => $type);

            if (!empty($format)) {
                $property['format'] = $format;
            }

            if ($type == 'text') {
                $property['analyzer'] = 'french';
                $property['fields'] = array(
                    "raw"     => array(
                        "type"         => "keyword",
                        "ignore_above" => 256,
                        "index"        => true
                    ),
                    "keyword" => array(
                        "type"         => "keyword",
                        "ignore_above" => 256,
                        "index"        => true
                    )
                );
            }

            if ($type == 'keyword') {
                $property['ignore_above'] = 1024;
            }

            $mapping['body']['properties'][$fieldName] = $property;
        }

        $client->indices()->putMapping($mapping);
    }

    /**
     * Get ES client
     *
     * @return \Elasticsearch\Client|ClientBuilder
     */
    protected function getElasticsearchClient()
    {
        if (is_null($this->esClient)) {
            $hosts = $this->getElasticsearchHosts();
            $this->esClient = ClientBuilder::create()->setHosts($hosts)->build();
        }

        return $this->esClient;
    }

    /**
     * Get PDO connection
     *
     * @return PDO
     */
    protected function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get Elasticsearch hosts
     *
     * @return array
     */
    protected function getElasticsearchHosts()
    {
        return $this->esHosts;
    }

    /**
     * Import data
     *
     * @param string          $indexName Index name
     * @param string          $typeName  Type name
     * @param array           $fields    Fields
     * @param string          $query     SQL Query
     * @param OutputInterface $output    Output
     */
    protected function importData($indexName, $typeName, $fields, $query, OutputInterface $output)
    {
        $client = $this->getElasticsearchClient();

        $i = 0;
        $params = array();
        $queryFields = array();
        foreach ($fields as $fieldName => $fieldData) {
            $sqlField = $fieldData['field'];
            $queryFields[] = $sqlField . " as " . $fieldName;
        }

        $origQuery = str_replace('{fields}', implode(',', $queryFields), $query);

        $pageSize = 5000;
        $offset = 0;

        $db = $this->getConnection();
        $continue = true;
        while ($continue == true) {
            $query = str_replace('{limit}', $offset . ',' . $pageSize, $origQuery);
            $offset += $pageSize;

            /** @var \PDOStatement $statement */
            $statement = $db->prepare($query);

            $output->writeln(
                'Query: ' . $statement->queryString,
                OutputInterface::VERBOSITY_DEBUG
            );

            $statement->execute();
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (!count($rows)) {
                $continue = false;
            }

            $previousRowId = '';
            foreach ($rows as $rowData) {
                if ($rowData['id'] == $previousRowId) {
                    throw new \Exception('Dupplicated row with id "' . $previousRowId . '"');
                }
                $i++;

                $params['body'][] = [
                    'index' => [
                        '_index' => $indexName,
                        '_type'  => $typeName,
                        '_id'    => $rowData['id']
                    ]
                ];

                foreach ($fields as $fieldName => $fieldData) {
                    if (isset($fieldData['transform'])) {
                        try {
                            $rowData[$fieldName] = call_user_func_array($fieldData['transform'], array($rowData[$fieldName]));
                        } catch (\Exception $e) {
                            $output->writeln('<error>' . $e->getMessage());
                            $rowData[$fieldName] = '';
                        }
                    }
                }

                $params['body'][] = $rowData;

                $batchSize = 1000;
                if ($i % $batchSize == 0) {
                    $output->writeln("<info>Writing " . $batchSize . " documents to Elasticsearch ...");
                    $responses = $client->bulk($params);

                    if (!empty($responses) && is_array($responses)) {
                        $this->checkResponsesErrors($output, $responses);
                    }

                    $params = ['body' => []];

                    unset($responses);
                }

                $previousRowId = $rowData['id'];
            }
        }

        if (!empty($params['body'])) {
            $responses = $client->bulk($params);
        }

        if (!empty($responses) && is_array($responses)) {
            $this->checkResponsesErrors($output, $responses);
        }

        $output->writeln(
            sprintf('<info><options=bold>%d</options=bold> documents successfuly imported from database', $i)
        );
    }

    /**
     * Begin timer
     *
     * @param string $name Timer name
     *
     * @return void
     */
    protected function beginTimer($name)
    {
        $this->timers[$name] = microtime(true);
    }

    /**
     * End timer
     *
     * @param string $name Timer name
     *
     * @return int Duration in milliseconds (rounded)
     */
    protected function endTimer($name)
    {
        $end = microtime(true);
        $duration = $end - $this->timers[$name];

        return round($duration * 1000);
    }

    /**
     * @param OutputInterface $output
     * @param array $responses
     */
    protected function checkResponsesErrors(OutputInterface $output, array $responses): void
    {
        foreach ($responses as $response) {
            if (!empty($response['index']['error'])) {
                $output->writeln(sprintf('[%s] %s: %s',
                    $response['index']['_id'],
                    $response['index']['error']['type'],
                    $response['index']['error']['reason']
                ));
            }
        }
    }

    /**
     *
     * Get since formatted date from $input
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function getSince(InputInterface $input, OutputInterface $output)
    {
        $since = $input->getOption('since');
        $since = strtotime($since);
        if (!$since) {
            $since = strtotime(self::DEFAULT_SINCE);
            $output->write(sprintf('<info>Invalid since option supplied, using default (%s)</info>', self::DEFAULT_SINCE));
        }

        return gmdate('Y-m-d H:i:s', $since);
    }
}
