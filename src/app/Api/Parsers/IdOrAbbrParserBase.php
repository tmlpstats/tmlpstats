<?php
namespace TmlpStats\Api\Parsers;

/**
 * This is the base class for parsers which can look up an eloquent ORM object.
 *
 * To use, you inherit the class and override the two documented protected attributes,
 * and optional others. Also see @class(IdParserBase) for a variant which only parses
 * via ID instead of ID+abbr.
 *
 * Valid inputs:
 *    * 1234  (numeric): Lookup by ID
 *    * 'abcd' (slug style): Lookup by `abbreviation` field, if `allowAbbr` is true
 *    * ['id' => 1234] (obj): Lookup by ID, but with a representative object.
 *
 * IMPORTANT: This parser does not (and should not) perform authz, it will simply return
 * the object if the object exists by ID. It is the responsibility of the business logic
 * code to decide whether the object is appropriate for the user to work on.
 */
abstract class IdOrAbbrParserBase extends ParserBase
{
    // The following are required to be overridden in subclasses:
    protected $type = 'id'; // A string representing the informational type (short name) of this object.
    protected $class = ''; // The model class of this object.

    // The following may be overridden in subclasses:
    protected $allowAbbr = true; // If true, then allow finding the ORM model by `abbr` (slug)
    protected $allowObj = false; // If true, then allow JSON object(PHP array) input to fill the value
    protected $keyAttr = 'id'; // Change the key lookup attribute (advanced)

    // This is an internal value, primarily exposed for tests/mocks to use without hackery.
    protected $parsed = null;

    public function validate($value)
    {
        if (is_numeric($value)) {
            if ($value <= 0) {
                return false;
            }
        } else if (is_array($value)) {
            if (!$this->allowObj) {
                return false;
            }
        } else {
            // not numeric and obj not allowed, so check abbr string
            if (!$this->allowAbbr || !is_string($value) || !preg_match('/^[a-zA-Z]{2,5}$/', $value)) {
                return false;
            }
        }

        return $this->exists($value);
    }

    public function parse($id)
    {
        // Return the cached retrieved object if the ID matches
        if (($parsed = $this->parsed) != null && $parsed[0] == $id) {
            return $parsed[1];
        }

        if (is_array($id)) {
            if (!$this->allowObj) {
                throw new \Exception('Array input not allowed');
            }
            if (!array_key_exists($this->keyAttr, $id)) {
                return null;
            }
            $id = $id[$this->keyAttr];
        }

        return $this->fetch($this->class, $id);
    }

    /**
     * Check the item exists in the database. Will perform a DB lookup.
     * @param  mixed $id The ID or input representing ID
     * @return bool      `true` if the item can be found in the database.
     */
    public function exists($id)
    {
        $value = $this->parse($id);
        $this->parsed = [$id, $value];

        return ($value !== null);
    }

    /**
     * Actually perform the work of fetching the object from the database.
     * @param  string     $class A class reference of an Eloquent ORM model
     * @param  string|int $id    The ID or abbreviation to look up.
     * @return Model|null        The item if it exists, null otherwise
     */
    public function fetch($class, $id)
    {
        if (is_numeric($id)) {
            return $class::find(intval($id));
        } else {
            return $class::abbreviation($id)->first();
        }
    }
}
