<?php

namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;

class InterestForm extends Model
{

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'team_id',
        'vision_team',
        'regional_statistician_team',
    ];

    public function team()
    {
        return $this->hasOne('TmlpStats\Center', 'id', 'team_id');
    }

    public function scopeInEmail($query, $emails)
    {
        return $query->whereIn('email', $emails);
    }

}
