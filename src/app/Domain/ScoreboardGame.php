<?php
namespace TmlpStats\Domain;

use TmlpStats as Models;

/**
 * Represents one game (cap/cpc/etc) storing both promise and actual values.
 *
 * Contains the necessary logic by which to determine points and percentage.
 */
class ScoreboardGame
{
    public $key;
    private $promise = 0;
    private $actual = null;
    private $originalPromise = null;

    public function __construct($gameKey)
    {
        $this->key = $gameKey;
    }

    public function promise()
    {
        return $this->promise;
    }

    public function actual()
    {
        return $this->actual;
    }

    public function originalPromise()
    {
        return $this->originalPromise;
    }

    public function percent()
    {
        if ($this->actual == null) {
            return 0;
        }
        return round(static::calculatePercent($this->promise, $this->actual));
    }

    public function points()
    {
        // yes I know, this recalculates a lot of things. A little math never hurt anybody.
        return static::getPoints($this->key, $this->percent());
    }

    public function setPromise($promise)
    {
        $this->promise = $promise;
    }

    public function setOriginalPromise($promise)
    {
        $this->originalPromise = $promise;
    }

    public function setActual($actual)
    {
        $this->actual = $actual;
    }

    public function set($type, $value)
    {
        if ($type == 'promise') {
            $this->setPromise($value);
        } else if ($type == 'actual') {
            $this->setActual($value);
        } else if ($type == 'original') {
            $this->setOriginalPromise($value);
        } else {
            throw new \Exception("Unknown type {$type}");
        }
    }

    ////////////////////////////////////////////////////////
    /// STATIC FUNCTIONS TO DESCRIBE REUSABLE BUSINESS RULES

    public static function calculatePercent($promise, $actual)
    {
        return Models\Scoreboard::calculatePercent($promise, $actual);
    }

    public static function getPoints($gameKey, $percent)
    {
        // note the intentional inversion of these two values from the one on Scoreboard
        return Models\Scoreboard::getPoints($percent, $gameKey);
    }
}
