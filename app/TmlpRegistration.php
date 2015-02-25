<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TmlpRegistration extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'center_id',
        'first_name',
        'last_name',
        'reg_date',
        'incoming_team_year',
        'is_reviewer',
    ];

    protected $dates = array(
        'reg_date' => 'boolean',
    );

    protected $casts = array(
        'is_reviewer' => 'boolean',
    );

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function registrationData()
    {
        return $this->hasMany('TmlpStats\TmlpRegistrationData');
    }

}