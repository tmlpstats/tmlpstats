<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TmlpRegistration extends Model
{
    use CamelCaseModel;

    protected $fillable = [
        'person_id',
        'team_year',
        'incoming_quarter_id',
        'is_reviewer',
    ];

    protected $casts = array(
        'is_reviewer' => 'boolean',
    );

    public static function firstOrNew(array $attributes)
    {
        $person = Person::firstOrCreate([
            'center_id'  => $attributes['center_id'],
            'first_name' => $attributes['first_name'],
            'last_name'  => $attributes['last_name'],
        ]);

        return static::firstOrNew([
            'reg_date'  => $attributes['regDate'],
            'person_id' => $person->id,
        ]);
    }

    public function scopeTeamYear($query, $teamYear)
    {
        return $query->whereTeamYear($teamYear);
    }

    public function scopeIncomingQuarter($query, $quarter)
    {
        return $query->whereIncomingQuarterId($quarter->id);
    }

    public function scopeReviewer($query, $reviewer = true)
    {
        return $query->whereIsReviewer($reviewer);
    }

    public function person()
    {
        return $this->belongsTo('TmlpStats\Person');
    }

    public function incomingQuarter()
    {
        return $this->belongsTo('TmlpStats\Quarter', 'id', 'incoming_quarter_id');
    }

    public function registrationData()
    {
        return $this->hasMany('TmlpStats\TmlpRegistrationData');
    }
}
