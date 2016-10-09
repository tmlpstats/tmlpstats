<?php
namespace TmlpStats\Domain;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Api\Parsers;
use TmlpStats\Contracts\Referenceable;

/**
 * ParserDomain is a base class for implementing domain objects which are distinct enough
 * from ORM models that it makes sense to 'model' their behaviour in a consistent way.
 *
 * The helpers it includes are:
 * - Validation and coercion of input using our API Parsers
 * - Proxying an array of values as attributes
 * - Constructors from array and ways to fill it
 * - Track which values were set/changed for safer updates
 */
class ParserDomain implements Arrayable, \JsonSerializable, Referenceable
{
    // Make sure you prefix all properties in this and derived classes or
    // you're likely to have a bad time with the magic getter/setter methods
    protected $_values = [];
    protected $_setValues = [];

    protected $_refProp = 'id';

    public function __construct()
    {
        // do nothing! Avoids a weird bug in php when calling new static();
    }

    /**
     * Return the id that should be used as a reference for validation results
     *
     * @return string
     */
    public function getKey()
    {
        $prop = $this->_refProp;
        return $this->$prop;
    }

    /**
     * Return an array of information used to identify the reference
     *
     * @param  array $supplemental  Optional additional fields
     * @return array
     */
    public function getReference($supplemental = [])
    {
        return array_merge([
            'id' => $this->getKey(),
            'type' => class_basename($this),
        ], $supplemental);
    }

    /**
     * Implementation for Arrayable
     *
     * @return array
     */
    public function toArray()
    {
        $output = [];
        foreach ($this->_values as $k => $v) {
            if (!isset(static::$validProperties[$k])) {
                // Skip any Id properties that were added
                continue;
            }

            $conf = static::$validProperties[$k];

            if ($v !== null) {
                if ($conf['type'] == 'date') {
                    $v = $v->toDateString();
                } else if (array_get($conf, 'assignId', false)) {
                    $idProp = array_get($conf, 'idProp', 'id');
                    $v = $v->$idProp;
                }
            }
            $output[$k] = $v;
        }

        return $output;
    }

    /**
     * Implementation for JsonSerializable interface
     *
     * @return array  Array that is serializable with json_encode()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Create a new domain object from an array, parsing all values according to $validProperties.
     *
     * @param  array  $input           Flat array of input values
     * @param  array  $requiredParams  An array of required keys in $input
     * @return ParserDomain            The constructed object of whatever domain type
     */
    public static function fromArray($input, $requiredParams = [])
    {
        $obj = new static();
        $obj->updateFromArray($input);

        return $obj;
    }

    /**
     * Fill/update values in this domain from an array.
     *
     * @param  array  $input           Flat array of input values
     * @param  array  $requiredParams  An array of required keys in $input
     */
    public function updateFromArray($input, $requiredParams = [])
    {
        $parsed = static::parseInput($input, $requiredParams);
        foreach ($parsed as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * Clear the 'set' flags, useful if updating an object from input.
     */
    public function clearSetValues()
    {
        foreach ($this->_setValues as $k => $v) {
            $this->_setValues[$k] = false;
        }
    }

    public function __get($key)
    {
        if (isset($this->_values[$key])) {
            return $this->_values[$key];
        } else {
            return null;
        }
    }

    public function __set($key, $value)
    {
        if (!array_key_exists($key, static::$validProperties)) {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __set(): ' . $key .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE
            );

            return;
        }

        if (array_get(static::$validProperties[$key], 'assignId', false)) {
            $idProp = array_get(static::$validProperties[$key], 'idProp', 'id');
            $this->_values["{$key}Id"] = $value ? $value->$idProp : null;
        }

        $this->_values[$key] = $value;
        $this->_setValues[$key] = true;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->_values);
    }

    /**
     * Parse input array with validation and proper casting
     *
     * @param  array  $input           Flat array of input values
     * @param  array  $requiredParams  Array of required keys in $input
     * @return array                   Array of parsed and validated inputs
     */
    public static function parseInput($input, $requiredParams = [])
    {
        return Parsers\DictInput::parse(static::$validProperties, $input, $requiredParams);
    }

    /**
     * Copy an item onto a target object, at attribute $k.
     *
     * Also Does some fancypants stuff to avoid sets of equal values, including
     * a special case for Carbon dates
     *
     * @param  Object $target The target object to write onto
     * @param  string $k      The key to write
     * @param  mixed $v       The value to write.
     */
    protected function copyTarget($target, $k, $v, $conf = null)
    {
        if ($target == null) {
            return;
        }

        $existing = $target->$k;
        if ($existing instanceof Carbon) {
            if ($existing->ne($v)) {
                $target->$k = $this->$k;
            }
        } else if ($existing !== $v) {
            if (!$conf !== null && array_get($conf, 'assignId')) {
                $k_id = $k . 'Id';
                $id_prop = array_get($conf, 'idProp', 'id');
                $target->$k_id = $v->$id_prop;
            } else {
                $target->$k = $v;
            }
        }
    }
}
