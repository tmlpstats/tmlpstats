<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class TempScoreboard extends Model
{
    use CamelCaseModel;

    protected $table = 'temp_scoreboard';
    protected $dates = [
        'expires_at',
    ];
    protected $guarded = ['created_at'];

    /**
     * Returns all the records from a center that are valid (not expired)
     * @param  int $centerId  Center ID
     * @param  Carbon $threshold  Threshold date
     * @return array  Key=>scoreboard entry
     */
    public static function allValidFromCenter($centerId, $threshold = null)
    {
        $entries = static::whereCenterId($centerId)
            ->where('updated_at', '>', $threshold)->get();

        return static::entriesByRoutingKey($entries);
    }

    protected static function entriesByRoutingKey($entries)
    {
        $result = [];
        foreach ($entries as $entry) {
            $result[$entry->routingKey] = $entry;
        }
        return $result;
    }
}
