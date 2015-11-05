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

    public function __get($name)
    {
        switch ($name) {

            case 'firstName':
            case 'lastName':
                return $this->person->$name;
            case 'quarterNumber':

                $thisQuarter = Quarter::getCurrentQuarter($this->person->center->region);

                if (!$thisQuarter) {
                    return null;
                }

                $quarterNumber = $thisQuarter->quarterNumber;
                if ($thisQuarter->quarterNumber <= $this->incomingQuarter->quarterNumber) {
                    $quarterNumber += 4;
                }

                return $quarterNumber - $this->incomingQuarter->quarterNumber;
            default:
                return parent::__get($name);
        }
    }

    public static function firstOrNew(array $attributes)
    {
        $center = Center::find($attributes['center_id']);
        $incomingQuarter = Quarter::find($attributes['incoming_quarter_id']);
        $incomingQuarter->setRegion($center->region);

        $quarterStartDateString = $incomingQuarter->startWeekendDate->toDateString();

        $person = Person::firstOrCreate([
            'center_id'  => $attributes['center_id'],
            'first_name' => $attributes['first_name'],
            'last_name'  => $attributes['last_name'],
            'identifier' => "q:{$quarterStartDateString}:{$attributes['team_year']}",
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

    public function scopeByPerson($query, Person $person)
    {
        return $query->wherePersonId($person->id);
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
        return $this->belongsTo('TmlpStats\Quarter', 'incoming_quarter_id', 'id');
    }

    public function teamMemberData()
    {
        return $this->hasMany('TmlpStats\TeamMemberData');
    }
}
