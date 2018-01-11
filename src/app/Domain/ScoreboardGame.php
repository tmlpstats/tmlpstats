<?php
namespace TmlpStats\Domain;

/**
 * Represents one game (cap/cpc/etc) storing both promise and actual values.
 *
 * Contains the necessary logic by which to determine points and percentage.
 */
class ScoreboardGame
{
    const MAX_POINTS = 28;
    const MIN_POINTS = 0;
    protected static $ratingsByPoints = [
        '28' => 'Powerful',
        '22' => 'High Performing',
        '16' => 'Effective',
        '9' => 'Marginally Effective',
        '0' => 'Ineffective',
    ];
    protected static $pointsByPercent = [
        '100' => 4,
        '90' => 3,
        '80' => 2,
        '75' => 1,
    ];

    protected static $games = [
        'cap' => ['x' => 2],
        'cpc' => ['x' => 1],
        't1x' => ['x' => 1],
        't2x' => ['x' => 1],
        'gitw' => ['x' => 1],
        'lf' => ['x' => 1],
    ];

    public $key;
    private $promise = null;
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

        $percent = static::calculatePercent($this->promise, $this->actual);

        return static::normalizePercent($percent);
    }

    public function points()
    {
        // yes I know, this recalculates a lot of things. A little math never hurt anybody.
        return static::getPoints($this->key, $this->percent());
    }

    public function setPromise($promise)
    {
        $this->promise = $promise;
        if (is_numeric($promise)) {
            $this->promise = (int) $promise;
        }
    }

    public function setOriginalPromise($promise)
    {
        $this->originalPromise = $promise;
        if (is_numeric($promise)) {
            $this->originalPromise = (int) $promise;
        }
    }

    public function setActual($actual)
    {
        $this->actual = $actual;
        if (is_numeric($actual)) {
            $this->actual = (int) $actual;
        }
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

    /**
     * Get the integer percentage of actual performance against promise
     *
     * @param integer $actual
     * @param integer $promise
     *
     * @return float
     */
    public static function calculatePercent($promise, $actual)
    {
        if ($promise <= 0) {
            return 0;
        }

        $percent = ($actual / $promise) * 100;

        return max($percent, 0);
    }

    /**
     * Get the points based on game percentage
     *
     * @param string $game
     * @param float $percent
     *
     * @return integer
     * @throws \Exception if $game is not recognized
     */
    public static function getPoints($game, $percent)
    {
        $game = strtolower($game);
        $points = 0;

        if (!isset(static::$games[$game])) {
            throw new \Exception("Unknown game {$game}");
        }

        $percent = static::normalizePercent($percent);

        foreach (static::$pointsByPercent as $gamePercent => $gamePoints) {
            if ($percent >= $gamePercent) {
                $points = $gamePoints;
                break;
            }
        }

        $multiplier = isset(static::$games[$game]['x'])
            ? static::$games[$game]['x']
            : 1;

        return ($points * $multiplier);
    }

    /**
     * Get the rating based on number of points
     *
     * @param integer $points
     *
     * @return string
     * @throws \Exception if $points is out of range
     */
    public static function getRating($points)
    {
        if ($points > static::MAX_POINTS || $points < static::MIN_POINTS) {
            throw new \Exception("Points {$points} is out of range.");
        }

        foreach (static::$ratingsByPoints as $ratingPoints => $rating) {
            if ($points >= $ratingPoints) {
                return $rating;
            }
        }

        return static::$ratingsByPoints[static::MIN_POINTS];
    }

    /**
     * Normalize percent for use to calculate points
     *
     * Round on half value, except between 99.5 and 100
     *
     * @param  float $percent
     * @return integer
     */
    protected static function normalizePercent($percent)
    {
        if ($percent >= 99.5 && $percent < 100) {
            return 99;
        }

        // The spreadsheet rounds using a formula that looks like this:
        // =IF(Z22=""," ",IF(Z22>0.745,IF(Z22>0.795,IF(Z22>0.895,IF(Z22>0.995,4,3),2),1),0))
        //
        // Decoded, this rounds up from .5, but is slightly off in that 74.50000...00 is rounded down to 74
        // but 74.50000...01 is rounded up.
        //
        // Here, round() does the right thing with a slight difference in behavior.
        return round($percent);
    }
}
