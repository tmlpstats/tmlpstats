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
        'team',
        'vision_team',
        'regional_statistician_team',
    ];

    public function formatPhone()
    {
        return Util::formatPhone($this->phone);
    }

    public function tmlp_participant_team() {
        return $this->hasOne('TmlpStats\Center', 'tmlp_participant_team_id');
    }

}
