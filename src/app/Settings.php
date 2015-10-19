<?php

namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Setting extends Model
{
    use CamelCaseModel;

    protected $fillable = [
        'center_id',
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
     * @return Setting|null
     */
    public static function get($name, Center $center = null)
    {
        $setting = null;

        if ($center) {
            $setting = Settings::byCenter($center)
                ->name($name)
                ->active()
                ->first();
        }

        if (!$setting) {
            $setting = Settings::whereNull('center_id')
                ->name($name)
                ->active()
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

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }
}
