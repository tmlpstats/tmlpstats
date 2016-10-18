<?php
namespace TmlpStats\Traits;

use Eloquence\Database\Model;

/**
 * Provides a method to reduce last names to the sortest unique length.
 */
trait SanitizesLastNames
{
    /**
     * Shorten the last name to the shortest unique name in list
     *
     * @param  array   $people       List of people to sanitize
     * @param  boolean $useIdAsIndex True
     * @return array                 People with last name modified
     */
    protected function sanitizeNames($people, $useIdAsIndex = true)
    {
        $nameHash = [];
        foreach ($people as $id => $person) {
            $thisGroup = [];
            if (isset($nameHash[$person->firstName])) {
                $thisGroup = $nameHash[$person->firstName];
            }

            $thisGroup = $this->pushInto($thisGroup, '', $person->lastName, $person);
            $nameHash[$person->firstName] = $thisGroup;
        }

        $result = [];
        foreach ($nameHash as $firstName => $peopleByFirstName) {

            if ($firstName == 'Emma') {
                dd(array_dot($peopleByFirstName));
            }

            foreach(array_dot($peopleByFirstName) as $key => $person) {
                $newLastName = str_replace('.', '', $key);

                // If the new last name is longer than the old one, then it has
                // a numeric index appended because there are multiple people with
                // exactly the same last name. Drop the digit
                if (strlen($newLastName) <= strlen($person->lastName)) {
                    $person->lastName = $newLastName;
                }

                $result[$person->id] = $person;
            }
        }

        return $useIdAsIndex ? $result : array_flatten($result);
    }

    /**
     * Recursively push the object into an array keyed by letter
     *     e.g.
     *       $arr[A][a][a][a] = [person with last name "Aaaag"]
     *       $arr[A][a][a][e] = [person with last name "Aaaef"]
     *       $arr[A][a][b]    = [person with last name "Aabcd"]
     *
     * @param  array  $arr   Array to push into
     * @param  string $key   Base key in dot notation
     * @param  string $value Last name value to push
     * @param  object $data  object with lastName property
     * @return array
     */
    protected function pushInto($arr, $key, $value, $data)
    {
        $newKey = $key ? "{$key}.{$value[0]}" : $value[0];

        $existing = array_get($arr, $newKey);

        if (is_array($existing)) {
            // There's a list of people at this level, recurse until we find a leaf
            $arr = $this->pushInto($arr, $newKey, substr($value, 1), $data);
        } else if ($existing !== null) {
            // This node already has another person, fork into an array

            // Get the position with the first unique character between the two strings
            $uniquePos = strspn($data->lastName ^ $existing->lastName, "\0");

            $same = substr($data->lastName, 0, $uniquePos);
            $sharedKey = implode('.', str_split($same));

            // Move existing data to new key
            if ($existing->lastName == $same) {
                // If the last name is the same length as the current key, add a child with an empty key
                array_set($arr, "{$sharedKey}.", $existing);
            } else {
                array_set($arr, "{$sharedKey}.{$existing->lastName[$uniquePos]}", $existing);
            }

            // Add new data
            if ($data->lastName == $same) {
                // If the last name is the same length as the current key, add a child with an empty key
                array_set($arr, "{$sharedKey}.", $data);
            } else {
                array_set($arr, "{$sharedKey}.{$data->lastName[$uniquePos]}", $data);
            }

            // If the two names have the same value, overwrite the empty key with a list of people with the
            // same last name
            if ($existing->lastName == $data->lastName) {
                array_set($arr, "{$sharedKey}.", [$existing, $data]);
            }
        } else {
            // No people at this level, add it
            array_set($arr, $newKey, $data);
        }

        return $arr;
    }
}
