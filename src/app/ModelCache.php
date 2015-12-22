<?php
namespace TmlpStats;

/**
 * Class ModelCache
 * @package TmlpStats
 *
 * Maintain an in memory object cache.
 * Simplifies reusing objects and relationships across a single transaction
 *
 */
class ModelCache
{
    protected static $modelCache = [];

    /**
     * Get class instance (easy chaining)
     * e.g.
     *    ModelCache::create()->get('key', 132);
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Get an object from the cache
     *
     * @param $key Object Key Name
     * @param $id  Object Id
     *
     * @return null|object Object from cache
     */
    public function get($key, $id)
    {
        return isset(static::$modelCache[$key][$id])
            ? static::$modelCache[$key][$id]
            : null;
    }

    /**
     * Add an object to the cache
     *
     * @param $key   Object Key Name
     * @param $id    Object id
     * @param $value New object to save
     */
    public function set($key, $id, $value)
    {
        static::$modelCache[$key][$id] = $value;
    }

    /**
     * Remove an object from the cache
     *
     * @param      $key Object Key Name
     * @param null $id  Object id, or null to remove all of this type
     */
    public function forget($key, $id = null)
    {
        if ($id !== null) {
            unset(static::$modelCache[$key][$id]);
        } else {
            unset(static::$modelCache[$key]);
        }
    }

    /**
     * Clear the entire cache
     */
    public function flush()
    {
        static::$modelCache = [];
    }
}
