<?php

namespace ClickAndMortar\Database\Command;

use Feeder\Cache\File as FileCache;
use Feeder\Command\ElasticsearchCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleCommand extends ElasticsearchCommand
{
    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('example')
            ->setDescription('Example command');
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
        parent::execute($input, $output);

        $cache = new FileCache();

        $fields = array(
            'id' => array('field' => 't1.id', 'type' => 'integer'),
            'name' => array('field' => 't2.name', 'type' => 'keyword'),
            'created_at' => array('field' => 't2.created_at', 'type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'),
            'tags' => array('field' => 't3.tags', 'type' => 'keyword', 'transform' => function($data) {
                return explode(',', $data);
            })
        );

        $indexName = 'dataidx';
        $typeName = 'data';

        try {

            $this->initializeElasticsearch($indexName, $typeName, $fields);

            $sql = "
SELECT {fields} 
FROM my_table t1
LEFT JOIN my_other_table t2 ON t2.parent_id = t1.id 
LEFT JOIN my_tags_table t3 ON t3.parent_id = t2.id 
ORDER by t2.created_at DESC 
LIMIT {limit}
";

            $this->importData($indexName, $typeName, $fields, $sql, $output);
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln($e->getTraceAsString());
        }
    }
}
