<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class ModelCachedRelationships extends Model
{
    use CamelCaseModel;

    public function getRelationshipFromMethod($method)
    {
        // I think this is a bug in Laravel's Eloquent Model where if they model is copied,
        // we don't bother checking if the relation was eager loaded so we get 2 lookups
        if (isset($this->relations[$method])) {
            return $this->relations[$method];
        }

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
