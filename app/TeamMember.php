<?php
namespace TmlpStats;

use TmlpStats\Quarter;
use TmlpStats\Person;
use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TeamMember extends Model
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

        return parent::firstOrNew([
            'team_year'           => $attributes['team_year'],
            'incoming_quarter_id' => $attributes['incoming_quarter_id'],
            'person_id'           => $person->id,
        ]);
    }

    public function getIncomingQuarter()
    {
        return Quarter::find($this->incomingQuarterId);
    }

    public function scopeTeamYear($query, $teamYear)
    {
        return $query->whereTeamYear($teamYear);
    }

    public function scopeIncomingQuarter($query, Quarter $quarter)
    {
        return $query->whereIncomingQuarterId($quarter->id);
    }

    public function scopePerson($query, Person $person)
    {
        return $query->whereIncomingQuarterId($person->id);
    }

    public function scopeReviewer($query, $reviewer = true)
    {
        return $query->whereIsReviewer($reviewer);
    }

    public function person()
    {
        return $this->belongsTo('TmlpStats\Person');
    }

    public function teamMemberData()
    {
        return $this->hasMany('TmlpStats\TeamMemberData');
    }
}
