<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class TeamMember extends Model
{
    use CamelCaseModel, CachedRelationships;

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
            case 'center':
                return $this->person->$name;
            case 'quarterNumber':

                $key = "quarterNumber";

                return static::getFromCache($key, $this->incomingQuarterId, function() {
                    return static::getQuarterNumber($this->incomingQuarter, $this->person->center->region);
                });
            case 'incomingQuarter':
                $key = "incomingQuarter:region{$this->center->regionId}";
                return static::getFromCache($key, $this->incomingQuarterId, function() {
                    $quarter = Quarter::find($this->incomingQuarterId);
                    if ($quarter) {
                        $quarter->setRegion($this->center->region);
                    }
                    return $quarter;
                });
            default:
                return parent::__get($name);
        }
    }

    public static function getQuarterNumber(Quarter $incomingQuarter, Region $region)
    {
        $thisQuarter = Quarter::getCurrentQuarter($region);
        if (!$thisQuarter) {
            return null;
        }

        $quarterNumber = $thisQuarter->quarterNumber;
        if ($thisQuarter->quarterNumber < $incomingQuarter->quarterNumber) {
            $quarterNumber += 4;
        }

        return $quarterNumber - $incomingQuarter->quarterNumber + 1;
    }

    public static function firstOrNew(array $attributes)
    {
        $center = Center::find($attributes['center_id']);

        $incomingQuarter = Quarter::findForCenter($attributes['incoming_quarter_id'], $center);

        $quarterStartDateString = $incomingQuarter->startWeekendDate->toDateString();

        // This identifier is designed to help us distinguish between 'people' from a center
        // with the same name. It's not perfect, but it should work well enough
        $identifier = "q:{$quarterStartDateString}:{$attributes['team_year']}";

        // Try to find them by name only first
        $people = Person::where('center_id', $attributes['center_id'])
            ->where('first_name', $attributes['first_name'])
            ->where('last_name', $attributes['last_name'])
            ->get();

        $person = null;
        if ($people->count() == 1) {
            // If there's only one, great!
            $person = $people->first();
        } else if ($people->count() > 1) {
            // If there's more than one, let's try to narrow it down
            $person = $people->where('identifier', $identifier)->first();
        }

        if (!$person) {
            // Still haven't found them? Fine, create a new one then
            $person = Person::create([
                'center_id'  => $attributes['center_id'],
                'first_name' => $attributes['first_name'],
                'last_name'  => $attributes['last_name'],
                'identifier' => $identifier,
            ]);
        } else if ($person->identifier != $identifier && isset($attributes['team_quarter']) && $attributes['team_quarter'] == 1) {

            // Only update it on the first week of the quarter and only if this is an incoming Q1
            $center = static::getFromCache('center', $attributes['center_id'], function () use ($attributes) {
                return Center::find($attributes['center_id']);
            });

            if (Quarter::isFirstWeek($center->region)) {
                $person->identifier = $identifier;
                $person->save();
            }
        }

        return parent::firstOrNew([
            'team_year'           => $attributes['team_year'],
            'incoming_quarter_id' => $attributes['incoming_quarter_id'],
            'person_id'           => $person->id,
        ]);
    }

    public function getIncomingQuarter()
    {
        return Quarter::findForCenter($this->incomingQuarterId, $this->person->center);
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
