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
        'wbo',
        'rereg',
        'excep',
        'travel',
        'room',
        'comment',
        'gitw',
        'tdo',
        'rpp_cap',
        'rpp_cpc',
        'rpp_lf',
    ];

    protected $casts = [
        'at_weekend' => 'boolean',
        'xfer_out'   => 'boolean',
        'xfer_in'    => 'boolean',
        'ctw'        => 'boolean',
        'wbo'        => 'boolean',
        'rereg'      => 'boolean',
        'excep'      => 'boolean',
        'travel'     => 'boolean',
        'room'       => 'boolean',
        'gitw'       => 'boolean',
        'tdo'        => 'integer',
        'rpp_cap'    => 'integer',
        'rpp_cpc'    => 'integer',
        'rpp_lf'     => 'integer',
    ];

    public function __get($name)
    {
        switch ($name) {
            case 'firstName':
            case 'lastName':
            case 'fullName':
            case 'shortName':
                return $this->teamMember->person->$name;
            case 'center':
                return $this->statsReport->center;
            case 'teamYear':
            case 'quarterNumber':
            case 'incomingQuarter':
                return $this->teamMember->$name;
            case 'rpp':
                return [
                    'cap' => $this->rppCap,
                    'cpc' => $this->rppCpc,
                    'lf' => $this->rppLf,
                ];
            default:
                return parent::__get($name);
        }
    }

    public function isActiveMember()
    {
        return ($this->withdrawCodeId === null && !$this->wbo && !$this->xferOut);
    }

    public function scopeByStatsReport($query, StatsReport $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function scopeByTeamMember($query, TeamMember $member)
    {
        return $query->whereTeamMemberId($member->id);
    }

    public function scopeWithdrawn($query)
    {
        return $query->whereNotNull('withdraw_code_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('withdraw_code_id')
                     ->where('xfer_out', 0)
                     ->where('wbo', 0);
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
