<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TeamMemberData extends Model {

    use CamelCaseModel;

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
        'xfer_out' => 'boolean',
        'xfer_in' => 'boolean',
        'ctw' => 'boolean',
        'rereg' => 'boolean',
        'room' => 'boolean',
    ];

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
        return $this->hasOne('TmlpStats\WithdrawCode');
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
