<?php
namespace TmlpStats\Traits;

use TmlpStats\ModelCache;

trait CachedRelationships
{
    public function getRelationshipFromMethod($method)
    {
        $relations = $this->$method();
        $relationKey = $relations->getForeignKey();

        $id = $this->$relationKey;
        if ($id === null) {
            return parent::getRelationshipFromMethod($method);
        }

        return static::getFromCache($method, $id, function() use ($method) {
            return parent::getRelationshipFromMethod($method);
        });
    }

    public static function getFromCache($method, $id, $default = null)
    {
        $cachedValue = ModelCache::create()->get($method, $id);
        if ($cachedValue === null) {

            if (is_callable($default)) {
                $cachedValue = $default();
            } else {
                $cachedValue = $default;
            }

            ModelCache::create()->set($method, $id, $cachedValue);
        }

        return $cachedValue;
    }
}
