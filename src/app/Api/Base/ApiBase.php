<?php
namespace TmlpStats\Api\Base;

use Cache;
use Illuminate\Database\Eloquent\Model;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Api\Parsers;
use TmlpStats\Domain\ParserDomain;
use TmlpStats\Validate\ApiValidationManager;

class ApiBase
{
    const CACHE_TTL = 1440 * 14; // 14 days

    /**
     * Is cache enabled by default?
     * @var bool
     */
    protected $cacheEnabled = false;

    /**
     * Methods to not cache.
     * @var array
     */
    protected $dontCache = [];

    /**
     * Working stack for keeping track of objects being cached
     * @var array
     */
    private $targetObjCache = [];

    /**
     * Array of valid properties and their config.
     *  example:
     *      'firstName' => [
     *          'owner' => 'person', // optionally specify secondary objects that this property belongs to
     *          'type' => 'string',  // property type, used for validation
     *      ],
     *
     * @var array
     */
    protected $validProperties = [];

    protected $context = null;

    public function __construct(Api\Context $context = null)
    {
        $this->context = $context;
    }

    /**
     * Run parsers on demand.
     *
     * @param       $data
     * @param array $requiredParams
     *
     * @return array
     */
    public function parseInputs($data, $requiredParams = [])
    {
        return Parsers\DictInput::parse($this->validProperties, $data, $requiredParams);
    }

    /**
     * Determine if we should use the cache
     *
     * @param $report
     *
     * @return bool
     */
    public function useCache($report)
    {
        return $this->cacheEnabled
        && env('API_USE_CACHE', $this->cacheEnabled)
        && !in_array($report, $this->dontCache);
    }

    /**
     * Get cache tags for provided list of objects
     *
     * Will ignore any value that is not an instance of Model
     *
     * @param array $objects
     *
     * @return array
     */
    public function getCacheTags($objects = [])
    {
        $tags = ['api'];
        foreach ($objects as $name => $object) {
            if (!($object instanceof Model)) {
                continue;
            }

            $basename = lcfirst(class_basename($object));
            $tags[] = "{$basename}{$object->id}";
        }

        return $tags;
    }

    /**
     * Get cache key from list of objects
     *
     * @param array $objects
     *
     * @return mixed
     */
    public function getCacheKey($objects = [])
    {
        // Canonicalize list by sorting on keys
        ksort($objects);

        $keyBase = '';
        foreach ($objects as $name => $value) {
            if ($value instanceof Model) {
                $value = $value->id;
            } else if (is_array($value) || is_object($value)) {
                if (is_array($value)) {
                    // Canonicalize list by sorting on keys
                    ksort($value);
                }
                $value = json_encode($value);
            }
            $keyBase .= ":{$name}{$value}";
        }

        return md5($keyBase);
    }

    /**
     * Return cache entry for list of objects
     *
     * @param $objects
     *
     * @return null
     */
    public function checkCache($objects)
    {
        $method = debug_backtrace()[1]['function'];
        if (!$this->useCache($method)) {
            return null;
        }
        $objects['method'] = $method;
        $this->targetObjCache[$method] = $objects;

        $tags = $this->getCacheTags($objects);
        $cacheKey = $this->getCacheKey($objects);

        return Cache::tags($tags)->get($cacheKey);
    }

    /**
     * Create cache entry for list of objects
     *
     * @param $objects
     * @param $value
     *
     * @return null
     */
    public function putCache($value, $objects = null)
    {
        $method = debug_backtrace()[1]['function'];
        if (!$this->useCache($method)) {
            return null;
        }

        if ($objects === null) {
            $objects = isset($this->targetObjCache[$method]) ? $this->targetObjCache[$method] : [];
            array_forget($this->targetObjCache, $method);
        }
        $objects['method'] = $method;

        $tags = $this->getCacheTags($objects);
        $cacheKey = $this->getCacheKey($objects);
        Cache::tags($tags)->put($cacheKey, $value, static::CACHE_TTL);
    }

    /**
     * Merge arrays of objects together. Works like array_merge but will handle non-array input for when it's not easy
     * to pre-check
     *
     * Useful when input may be null instead of and empty array
     *
     * @param $arr1
     * @param $arr2
     *
     * @return mixed
     */
    public function merge($arr1, $arr2)
    {
        if ($arr1 === null) {
            $arr1 = [];
        } else if (!is_array($arr1)) {
            $arr1 = [$arr1];
        }
        if ($arr2 === null) {
            $arr2 = [];
        } else if (!is_array($arr2)) {
            $arr2 = [$arr2];
        }

        return array_merge($arr1, $arr2);
    }

    public function validateAll(Models\StatsReport $statsReport, array $data, array $pastWeeks = [])
    {
        $validator = new ApiValidationManager($statsReport);

        $success = $validator->run($data, $pastWeeks);

        return [
            'valid' => $success,
            'messages' => $validator->getMessages(),
        ];
    }

    public function validateObject(Models\StatsReport $statsReport, $object, $id = null, array $pastWeeks = [])
    {
        $validator = new ApiValidationManager($statsReport);

        $success = $validator->runOne($object, $id, $pastWeeks);

        return [
            'valid' => $success,
            'messages' => $validator->getMessages(),
        ];
    }
}
