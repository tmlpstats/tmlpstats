<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class TeamMemberData extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $table = 'team_members_data';

    protected $fillable = [
        'stats_report_id',
        'team_member_id',
        'at_weekend',
        'xfer_out',
        'xfer_in',
        'ctw',
        'withdraw_code_id',
        'rereg',
        'excep',
        'travel',
        'room',
        'comment',
        'gitw',
        'tdo',
    ];

    protected $casts = [
        'at_weekend' => 'boolean',
        'xfer_out'   => 'boolean',
        'xfer_in'    => 'boolean',
        'ctw'        => 'boolean',
        'rereg'      => 'boolean',
        'excep'      => 'boolean',
        'travel'     => 'boolean',
        'room'       => 'boolean',
        'gitw'       => 'boolean',
        'tdo'        => 'boolean',
    ];

    public function __get($name)
    {
        switch ($name) {

            case 'firstName':
            case 'lastName':
            case 'center':
                return $this->teamMember->person->$name;
            case 'teamYear':
            case 'quarterNumber':
            case 'incomingQuarter':
                return $this->teamMember->$name;
            default:
                return parent::__get($name);
        }
    }

    public function isActiveMember()
    {
        return ($this->withdrawCodeId === null && !$this->xferOut);
    }

    public function scopeByStatsReport($query, StatsReport $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function scopeWithdrawn($query)
    {
        return $query->whereNotNull('withdraw_code_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('withdraw_code_id')
                     ->where('xfer_out', 0);
    }

    public function statsReport()
    {
        return $this->belongsTo('TmlpStats\StatsReport');
    }

    public function withdrawCode()
    {
        return $this->belongsTo('TmlpStats\WithdrawCode');
    }

    public function teamMember()
    {
        return $this->belongsTo('TmlpStats\TeamMember');
    }
}
