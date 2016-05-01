<?php
namespace TmlpStats\Api\Base;

use Cache;
use Illuminate\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Api\Parsers;

class ApiBase
{
    const CACHE_TTL = 1440 * 14;

    /**
     * Is cache enabled by default? Override this in derived classes
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * Methods to not cache. Override this in derived classes
     * @var array
     */
    protected $dontCache = [];

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

    public function __construct(Guard $auth, Request $request)
    {
        $this->user = $auth->user();
        $this->request = $request;
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
        $output = [];
        foreach ($data as $key => $value) {
            if (!isset($this->validProperties[$key])) {
                continue;
            }

            // The parsers expect data as ParameterBag objects
            if (is_array($data)) {
                $data = new ParameterBag($data);
            }

            $required = isset($requiredParams[$key]);

            $parser = Parsers\Factory::build($this->validProperties[$key]['type']);
            $output[$key] = $parser->run($data, $key, $required);
        }
        return $output;
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
        $tags = ["api"];
        foreach ($objects as $name => $object) {
            if (!($object instanceof Model)) {
                continue;
            }

            $basename = lcfirst(class_basename(get_class($object)));
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
        // Canonicalize list by sorting
        ksort($objects);

        $keyBase = '';
        foreach ($objects as $name => $value) {
            if ($value instanceof Model) {
                $value = $value->id;
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
    public function putCache($objects, $value)
    {
        $method = debug_backtrace()[1]['function'];
        if (!$this->useCache($method)) {
            return null;
        }
        $objects['method'] = $method;

        $tags = $this->getCacheTags($objects);
        $cacheKey = $this->getCacheKey($objects);
        Cache::tags($tags)->put($cacheKey, $value, static::CACHE_TTL);
    }

    /**
     * Merge arrays of objects together. Works like array_merge but will handle non-array input
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
}
