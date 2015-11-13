<?php
namespace TmlpStats;

class ModelCache
{
    protected static $modelCache = [];

    public static function create()
    {
        return new static();
    }

    public function get($key, $id)
    {
        return isset(static::$modelCache[$key][$id])
            ? static::$modelCache[$key][$id]
            : null;
    }

    public function set($key, $id, $value)
    {
        static::$modelCache[$key][$id] = $value;
    }
}
