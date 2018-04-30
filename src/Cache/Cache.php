<?php

namespace Feeder\Cache;

/**
 * Interface CacheInterface
 *
 * @package   ClickAndMortar\Cache
 * @author    Michael BOUVY <michael.bouvy@clickandmortar.fr>
 * @copyright Click & Mortar 2015
 */
interface Cache
{
    /**
     * Save value
     *
     * @param string $key   Key
     * @param mixed  $value Value
     * @param int    $ttl   Time To Live in seconds
     *
     * @return void
     */
    public function save($key, $value, $ttl = 0);

    /**
     * Get value
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Check if key exists
     *
     * @param string $key Key
     *
     * @return bool
     */
    public function exists($key);
}
