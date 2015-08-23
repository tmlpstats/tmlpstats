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

    public function scopeAccountability($query, $accountability)
    {
        return $query->whereAccountability($accountability);
    }

    public function scopeByCenter($query, $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeQuarter($query, $quarter)
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

    public function teamMember()
    {
        return $this->belongsTo('TmlpStats\TeamMember');
    }
}
