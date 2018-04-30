<?php

namespace Feeder\Cache;

/**
 * Class File
 *
 * @package Feeder\Cache
 * @author  Michael BOUVY <michael.bouvy@clickandmortar.fr>
 */
class File implements Cache
{
    /**
     * Default cache path
     */
    const DEFAULT_PATH = 'cache';

    /**
     * @var string
     */
    protected $path;

    /**
     * File constructor.
     *
     * @param string $path Storage path
     */
    public function __construct($path = self::DEFAULT_PATH)
    {
        $this->setPath($path);
    }

    /**
     * Get value
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $key = $this->formatKey($key);

        if ($this->exists($key)) {
            return unserialize(file_get_contents($this->getKeyPath($key)));
        }

        return null;
    }

    /**
     * Check if value exists
     *
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        $key = $this->formatKey($key);

        return file_exists($this->getKeyPath($key));
    }

    /**
     * Save value
     *
     * @param string $key
     * @param mixed  $value
     *
     * @param int    $ttl
     */
    public function save($key, $value, $ttl = 0)
    {
        $key = $this->formatKey($key);

        file_put_contents($this->getKeyPath($key), serialize($value));
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        if (!is_dir($path)) {
            mkdir($path);
        }

        $this->path = $path;
    }

    /**
     * Format key
     *
     * @param string $key
     *
     * @return string
     */
    public function formatKey($key)
    {
        return strtolower(preg_replace("/[^a-z0-9]/i", "-", $key));
    }

    /**
     * Get the full file path for key
     *
     * @param string $key
     *
     * @return string
     */
    protected function getKeyPath($key)
    {
        return $this->getPath() . DIRECTORY_SEPARATOR . $key;
    }
}
