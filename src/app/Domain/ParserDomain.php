<?php

namespace TmlpStats\Domain;

use Carbon\Carbon;
use TmlpStats\Api\Parsers;

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
class ParserDomain
{
    protected $values = [];
    protected $setValues = [];

    /**
     * Create a new domain object from an array, parsing all values according to $validProperties.
     * @param  array $input An array of values
     * @return The constructed object of whatever domain type
     */
    public static function fromArray($input, $requiredParams = [])
    {
        $obj = new static();
        $obj->fillFromArray($input);

        return $obj;
    }

    /**
     * Fill/update values in this domain from an array.
     * @param  array $input Flat array of input.
     * @return [type]        [description]
     */
    public function fillFromArray($input, $requiredParams = [])
    {
        $parsed = static::parseInput($input, $requiredParams);
        foreach ($parsed as $k => $v) {
            $this->values[$k] = $v;
            $this->setValues[$k] = true;
        }
    }

    /**
     * Clear the 'set' flags, useful if updating an object from input.
     */
    public function clearSetValues()
    {
        foreach ($this->setValues as $k => &$v) {
            $this->setValues[$k] = false;
        }
    }

    public function __get($key)
    {
        if (!isset(static::$validProperties[$key])) {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __get(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
        }
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        } else {
            return null;
        }
    }

    public function __set($key, $value)
    {
        if (!isset(static::$validProperties[$key])) {
            $trace = debug_backtrace();
            trigger_error(
                'Undefined property via __set(): ' . $name .
                ' in ' . $trace[0]['file'] .
                ' on line ' . $trace[0]['line'],
                E_USER_NOTICE);
        }
        $this->values[$key] = $value;
        $this->setValues[$key] = true;
    }

    /**
     * parseInput exists primarily to
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
    protected function copyTarget($target, $k, $v)
    {
        if ($target == null) {
            return;
        }
        $existing = $target->$k;
        if ($existing instanceof Carbon && $existing->ne($v)) {
            $target->$k = $this->$k;
        } else if ($existing !== $v) {
            $target->$k = $v;
        }

    }
}
