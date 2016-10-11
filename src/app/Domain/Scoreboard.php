<?php
namespace TmlpStats\Domain;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use TmlpStats\Contracts\Referenceable;

/**
 * Represents a six-game scoreboard (cap, cpc, t1x etc.)
 *
 * This is a mutable structure which can marshal to/from arrays.
 * It will also do some bonus things.
 */
class Scoreboard implements Arrayable, Referenceable
{
    const GAME_KEYS = ['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'];
    protected $games = [];
    public $meta = null;
    public $week = null;

    protected function __construct($week = null)
    {
        $this->week = $week;
        $this->meta = [];
        foreach (static::GAME_KEYS as $gameKey) {
            $this->games[$gameKey] = new ScoreboardGame($gameKey);
        }
    }

    /**
     * Return the id that should be used as a reference for validation results
     *
     * @return string
     */
    public function getKey()
    {
        return $this->week->toDateString();
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
            'type' => 'Scoreboard',
        ], $supplemental);
    }

    /** Create a scoreboard that's blank */
    public static function blank($week = null)
    {
        return new static($week);
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
        foreach ($this->games as $game) {
            $total += $game->points();
        }

        return $total;
    }

    /**
     * Calculate percent for this entire row
     * @return int Percent average; 0-100
     */
    public function percent()
    {
        $total = 0;
        foreach ($this->games as $game) {
            // Only include values between 0 - 100
            // Truncate negative and values greater than 100 to prevent a single game from
            // skewing the average
            $total += max(min($game->percent(), 100), 0);
        }

        return round($total / count($this->games));
    }

    /**
     * Rating for this row.
     * @return string Rating, e.g. "Ineffective", "Effective"
     */
    public function rating()
    {
        return ScoreboardGame::getRating($this->points());
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
        foreach ($this->games as $game) {
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
        $this->games[$gameKey]->set($type, $value);
    }

    ////////////
    /// Working with the commonly used array format

    public function parseArray($data)
    {
        if ($weekInput = array_get($data, 'week', null)) {
            $this->week = Carbon::parse($weekInput);
        }
        $games = array_get($data, 'games', null);

        foreach ($this->games as $gameKey => $game) {
            if ($games !== null && array_key_exists($gameKey, $games)) {
                $gameData = $games[$gameKey];
                $game->setPromise(array_get($gameData, 'promise', null));
                $game->setActual(array_get($gameData, 'actual', null));
                $game->setOriginalPromise(array_get($gameData, 'original', null));
            } else {
                if (($promise = array_get($data, "promise.{$gameKey}", null)) !== null) {
                    $game->setPromise($promise);
                }
                if (($actual = array_get($data, "actual.{$gameKey}", null)) !== null) {
                    $game->setActual($actual);
                }
                if (($original = array_get($data, "original.{$gameKey}", null)) !== null) {
                    $game->setOriginalPromise($original);
                }
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
            'percent' => [
                'total' => $this->percent(),
            ],
            'points' => [
                'total' => $this->points(),
            ],
            'rating' => $this->rating(),
            'games' => [],
            'meta' => $this->meta,
        ];

        if ($this->week) {
            $v['week'] = $this->week->toDateString();
        }

        $original = [];

        foreach ($this->games as $gameKey => $game) {
            $g = [];
            $v['promise'][$gameKey] = $g['promise'] = $game->promise();
            $v['actual'][$gameKey] = $g['actual'] = $game->actual();
            $v['percent'][$gameKey] = $g['percent'] = $game->percent();
            $v['points'][$gameKey] = $g['points'] = $game->points();

            if ($game->originalPromise()) {
                $original[$gameKey] = $g['original'] = $game->originalPromise();
            }

            // set the additional key for great format switch
            $v['games'][$gameKey] = $g;
        }

        // Only add the 'original' key if it was set by any of the games
        if (count($original) > 0) {
            $v['original'] = $original;
        }

        return $v;
    }

    /**
     * Return as an array in the new format, with none of the legacy stuff.
     * @return array [description]
     */
    public function toNewArray()
    {
        $v = $this->toArray();
        unset($v['promise'], $v['actual'], $v['percent'], $v['points']);

        return $v;
    }
}
