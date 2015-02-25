<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TeamMember extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'first_name',
        'last_name',
        'team_year',
        'accountability',
        'center_id',
        'completion_quarter_id',
    ];

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function teamMemberData()
    {
        return $this->hasMany('TmlpStats\TeamMemberData');
    }
}
