<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class ProgramTeamMember extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'center_id',
        'quarter_id',
        'team_member_id',
        'first_name',
        'last_name',
        'accountability',
        'offset',
        'phone',
        'email',
    ];

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }
}
