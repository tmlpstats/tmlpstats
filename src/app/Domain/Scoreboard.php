<?php
namespace TmlpStats\Domain;

use Illuminate\Contracts\Support\Arrayable;
use TmlpStats as Models;

/**
 * Represents a six-game scoreboard (cap, cpc, t1x etc.)
 *
 * This is a mutable structure which can marshal to/from arrays.
 * It will also do some bonus things.
 */
class Scoreboard implements Arrayable
{
    protected $games = [];

    protected function __construct()
    {
        foreach (Models\Scoreboard::GAME_KEYS as $gameKey) {
            $this->games[$gameKey] = new ScoreboardGame($gameKey);
        }
    }

    /** Create a scoreboard that's blank */
    public static function blank()
    {
        return new static();
    }

    /**
     * Create a scoreboard view from the typical array format
     * @return Scoreboard
     */
    public static function fromArray($data)
    {
        $scoreboard = static::blank();
        $scoreboard->parseArray($data);
        return $scoreboard;
    }

    ////////////
    /// Calculation / business logic

    /**
     * Calculate points for this entire row
     * @return int Points total; 0-24
     */
    public function points()
    {
        $total = 0;
        foreach ($this->games as &$game) {
            $total += $game->points();
        }
        return $total;
    }

    /**
     * Rating for this row.
     * @return string Rating, e.g. "Ineffective", "Effective"
     */
    public function rating()
    {
        return Models\Scoreboard::getRating($this->points());
    }

    public function game($gameKey)
    {
        return $this->games[$gameKey];
    }

    public function games()
    {
        return $this->games;
    }

    ////////////
    /// Helpers for client code (quick set/get, etc)

    /**
     * A neat little helper to loop through all the games.
     * @param  \Closure $callback A function callback which will get an instance of the game
     */
    public function eachGame(\Closure $callback)
    {
        foreach ($this->games as &$game) {
            //$bound = $callback->bindTo($game);
            $callback($game);
        }
    }

    /**
     * setValue is a shortcut for setting a value on a single key
     * @param string $gameKey The key of the game 'cap', 'cpc', etc
     * @param string $type    The type of value we're updating; 'promise', 'actual'
     * @param int    $value   The value we're setting this to.
     */
    public function setValue($gameKey, $type, $value)
    {
        $game = &$this->games[$gameKey];
        $game->set($type, $value);
    }

    ////////////
    /// Working with the commonly used array format

    public function parseArray($data)
    {
        foreach ($this->games as $gameKey => &$game) {
            if (($promise = array_get($data, "promise.{$gameKey}", null)) !== null) {
                $game->setPromise($promise);
            }
            if (($actual = array_get($data, "actual.{$gameKey}", null)) !== null) {
                $game->setActual($actual);
            }
        }
    }

    /**
     * Return as the "standard" array format
     * @return array
     */
    public function toBasicArray()
    {
        $v = $this->toArray();
        unset($v['games']);
        return $v;
    }

    public function toArray()
    {
        $v = [
            'promise' => [],
            'actual' => [],
            'percent' => [],
            'points' => [
                'total' => $this->points(),
            ],
            'rating' => $this->rating(),
            'games' => [],
        ];

        foreach ($this->games as $gameKey => &$game) {
            $g = [];
            $v['promise'][$gameKey] = $g['promise'] = $game->promise();
            $v['actual'][$gameKey] = $g['actual'] = $game->actual();
            $v['percent'][$gameKey] = $g['percent'] = $game->percent();
            $v['points'][$gameKey] = $g['points'] = $game->points();
            // set the additional key for great format switch
            $v['games'][$gameKey] = $g;
        }
        return $v;
    }

}
