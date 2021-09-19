<?php

namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'name',
    ];

    public function region()
    {
        return $this->belongsTo('TmlpStats\Region');
    }

    public function teams()
    {
        return $this->hasMany('TmlpStats\Center')->active();
    }

}
