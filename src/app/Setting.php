<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class Setting extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = [
        'center_id',
        'quarter_id',
        'name',
        'value',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get a setting by name
     * @param $name
     * @param Center|null $center
     * @param Quarter|null $quarter
     * @return Setting|null
     */
    public static function get($name, Center $center = null, Quarter $quarter = null)
    {
        $setting = static::getFromCache($name, $center ? $center->id : 0);
        if ($setting) {
            return $setting;
        }

        if ($center) {
            $setting = Setting::byCenter($center)
                ->name($name)
                ->active()
                ->where(function($query) use ($quarter) {
                    if ($quarter) {
                        $query->whereNull('quarter_id')
                              ->orWhere('quarter_id', $quarter->id);
                    }
                })
                ->orderBy('quarter_id', 'desc')
                ->first();
        }

        if (!$setting) {
            $setting = Setting::whereNull('center_id')
                ->name($name)
                ->active()
                ->where(function($query) use ($quarter) {
                    if ($quarter) {
                        $query->whereNull('quarter_id')
                              ->orWhere('quarter_id', $quarter->id);
                    }
                })
                ->orderBy('quarter_id', 'desc')
                ->first();
        }

        return $setting;
    }

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function scopeActive($query, $active = true)
    {
        return $query->whereActive($active);
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeByQuarter($query, Quarter $quarter)
    {
        return $query->whereQuarterId($quarter->id);
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function quarter()
    {
        return $this->belongsTo('TmlpStats\Quarter');
    }
}
