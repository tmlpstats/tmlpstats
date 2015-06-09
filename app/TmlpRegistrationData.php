<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TmlpRegistrationData extends Model {

    use CamelCaseModel;

    protected $table = 'tmlp_registrations_data';

    protected $fillable = [
        'reporting_date',
        'center_id',
        'quarter_id',
        'tmlp_registration_id',
        'offset',
        'bef',
        'dur',
        'aft',
        'weekend_reg',
        'app_out',
        'app_out_date',
        'app_in',
        'app_in_date',
        'appr',
        'appr_date',
        'wd',
        'wd_date',
        'committed_team_member_name',
        'committed_team_member_id',
        'comment',
        'incoming_weekend',
        'reason_withdraw',
        'travel',
        'room',
    ];

    protected $dates = [
        'reporting_date',
        'app_out_date',
        'app_in_date',
        'appr_date',
        'wd_date',
    ];

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function quarter()
    {
        return $this->belongsTo('TmlpStats\Quarter');
    }

    public function registration()
    {
        return $this->belongsTo('TmlpStats\TmlpRegistration');
    }

    public function scopeReportingDate($query, $date)
    {
        $dateStr = '';
        if (Util::isCarbonDate($date)) {
            $dateStr = $date->toDateString();
        } else {
            $dateStr = $date;
        }
        return $query->where('reporting_date', '=', $dateStr);
    }

    public function scopeCenter($query, $center)
    {
        return $query->where('center_id', '=', $center->id);
    }

    public function scopeIncomingWeekend($query, $weekend)
    {
        return $query->where('incoming_weekend', '=', $weekend);
    }
}
