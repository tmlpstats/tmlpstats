<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TeamMemberData extends Model {

    use CamelCaseModel;

    protected $table = 'team_members_data';

    protected $fillable = [
        'reporting_date',
        'center_id',
        'quarter_id',
        'team_member_id',
        'offset',
        'wknd',
        'xfer_out',
        'xfer_in',
        'ctw',
        'wd',
        'wbo',
        'rereg',
        'excep',
        'reason_withdraw',
        'travel',
        'room',
        'comment',
        'gitw',
        'tdo',
        'additional_tdo',
    ];

    protected $dates = [
        'reporting_date',
    ];

    public function setReportingDateAttribute($value)
    {
        $date = $this->asDateTime($value);
        $this->attributes['reporting_date'] = $date->toDateString();
    }
}
