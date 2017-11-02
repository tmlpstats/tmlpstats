<?php
namespace TmlpStats\Domain;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Represents the lock settings of a single week's scoreboard
 */
class ScoreboardLock implements Arrayable
{
    public $week;
    public $editPromise = false;
    public $editActual = false;

    public function __construct(Carbon $week)
    {
        $this->week = $week;
    }

    public function toArray()
    {
        return [
            'week' => $this->week->toDateString(),
            'editPromise' => $this->editPromise,
            'editActual' => $this->editActual,
        ];
    }

    public function isInteresting(): bool
    {
        return ($this->editPromise || $this->editActual);
    }

}
