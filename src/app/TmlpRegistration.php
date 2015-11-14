<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class TmlpRegistration extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = [
        'person_id',
        'team_year',
        'incoming_quarter_id',
        'reg_date',
        'is_reviewer',
    ];

    protected $dates = [
        'reg_date',
    ];

    protected $casts = [
        'is_reviewer' => 'boolean',
    ];

    public static function firstOrNew(array $attributes)
    {
        $regDateString = $attributes['reg_date']->toDateString();

        $person = Person::firstOrCreate([
            'center_id'  => $attributes['center_id'],
            'first_name' => $attributes['first_name'],
            'last_name'  => $attributes['last_name'],
            'identifier' => "r:{$regDateString}:{$attributes['team_year']}",
        ]);

        return parent::firstOrNew([
            'reg_date'  => $attributes['reg_date'],
            'team_year' => $attributes['team_year'],
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

    public function scopeByPerson($query, Person $person)
    {
        return $query->wherePersonId($person->id);
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
