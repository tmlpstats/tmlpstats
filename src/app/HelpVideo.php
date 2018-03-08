<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class HelpVideo extends Model
{
    use CamelCaseModel;

    protected $fillable = [
        'title',
        'description',
        'url',
        'access_group',
        'active',
        'order',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query, $active = true)
    {
        return $query->whereActive($active);
    }

    public function tags()
    {
        return $this->hasMany('TmlpStats\HelpVideoTag');
    }
}
