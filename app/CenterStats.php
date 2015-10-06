<?php
namespace TmlpStats;

use TmlpStats\CenterStatsData;
use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

// TODO: delete me after migration
class CenterStats extends Model
{
    use CamelCaseModel;

    protected $fillable = [
        'reporting_date',
        'center_id',
        'quarter_id',
    ];

    protected $dates = [
        'reporting_date',
    ];

    public function setReportingDateAttribute($value)
    {
        $date = $this->asDateTime($value);
        $this->attributes['reporting_date'] = $date->toDateString();
    }

    public function promiseData()
    {
        return $this->hasOne('TmlpStats\CenterStatsData', 'id', 'promise_data_id');
    }

    public function revokedData()
    {
        return $this->hasOne('TmlpStats\CenterStatsData', 'id', 'revoked_promise_data_id');
    }

    public function actualData()
    {
        return $this->hasOne('TmlpStats\CenterStatsData', 'id', 'actual_data_id');
    }

    public function statsReport()
    {
        return $this->belongsTo('TmlpStats\StatsReport');
    }
}
