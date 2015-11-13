<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class ModelCachedRelationships extends Model
{
    use CamelCaseModel;

    public function getRelationshipFromMethod($method)
    {
        $relations = $this->$method();
        $relationKey = $relations->getForeignKey();

        $id = $this->$relationKey;
        if ($id === null) {
            return parent::getRelationshipFromMethod($method);
        }

        $cachedValue = ModelCache::create()->get($method, $id);
        if ($cachedValue === null) {
            $cachedValue = parent::getRelationshipFromMethod($method);
            ModelCache::create()->set($method, $id, $cachedValue);
        }

        return $cachedValue;
    }
}
