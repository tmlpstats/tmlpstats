<?php
namespace TmlpStats;

class TeamMemberData extends ModelCachedRelationships
{
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
        'accountability_id',
        'gitw',
        'tdo',
    ];

    protected $casts = [
        'at_weekend' => 'boolean',
        'xfer_out'   => 'boolean',
        'xfer_in'    => 'boolean',
        'ctw'        => 'boolean',
        'rereg'      => 'boolean',
        'room'       => 'boolean',
    ];

    public function __get($name)
    {
        switch ($name) {

            case 'firstName':
            case 'lastName':
            case 'center':
                return $this->teamMember->person->$name;
            default:
                return parent::__get($name);
        }
    }

    public function scopeByStatsReport($query, StatsReport $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function scopeWithdrawn($query)
    {
        return $query->whereNotNull('withdraw_code_id');
    }

    public function statsReport()
    {
        return $this->belongsTo('TmlpStats\StatsReport');
    }

    public function withdrawCode()
    {
        return $this->belongsTo('TmlpStats\WithdrawCode');
    }

    public function accountability()
    {
        return $this->belongsTo('TmlpStats\Accountability');
    }

    public function teamMember()
    {
        return $this->belongsTo('TmlpStats\TeamMember');
    }
}
