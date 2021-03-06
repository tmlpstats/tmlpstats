<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats as Models;
use TmlpStats\Encapsulations;
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
            case 'fullName':
            case 'shortName':
            case 'center':
            case 'accountabilities':
                return $this->person->$name;
            case 'quarterNumber':
                return static::getQuarterNumber($this->incomingQuarter, $this->person->center->region);
            case 'incomingQuarter':
                $key = "incomingQuarter:region{$this->center->regionId}";

                return static::getFromCache($key, $this->incomingQuarterId, function () {
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

    public function getAccountabilities(Carbon $when)
    {
        return $this->person->getAccountabilities($when);
    }

    protected static $qReportingDate = null;

    // Hack the reporting date for the purpose of getQuarterNumber.
    // This is a temporary hack to solve a specific website issue with how we use reportingDate.
    public static function hackReportingDate(Carbon $reportingDate)
    {
        static::$qReportingDate = $reportingDate;
    }

    public static function getQuarterNumber(Quarter $incomingQuarter, Region $region)
    {
        $reportingDate = static::$qReportingDate;
        $candidateDate = $reportingDate ? $reportingDate->toDateString() : 'unknown';

        return static::getFromCache("quarterNumber:region{$region->id}:{$candidateDate}", $incomingQuarter->id, function () use ($incomingQuarter, $region, $reportingDate) {

            if ($reportingDate) {
                $thisQuarter = Encapsulations\RegionReportingDate::ensure($region, $reportingDate)->getQuarter();
            } else {
                $thisQuarter = Quarter::getCurrentQuarter($region);
            }

            if (!$thisQuarter) {
                return null;
            }

            $quarterNumber = $thisQuarter->quarterNumber;
            if ($thisQuarter->quarterNumber < $incomingQuarter->quarterNumber) {
                $quarterNumber += 4;
            }

            return $quarterNumber - $incomingQuarter->quarterNumber + 1;
        });
    }

    public static function findAppropriatePerson(Models\Center $center, string $firstName, string $lastName, int $teamYear, Models\Quarter $incomingQuarter)
    {

        $quarterStartDateString = $incomingQuarter->getCenterQuarter($center)->startWeekendDate->toDateString();

        // This identifier is designed to help us distinguish between 'people' from a center
        // with the same name. It's not perfect, but it should work well enough
        $identifier = "q:{$quarterStartDateString}:{$teamYear}";

        // Try to find them by name only first
        $people = Person::where('center_id', $center->id)
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->get();

        $person = null;
        if ($people->count() > 1) {
            // If there's more than one, let's try to narrow it down
            $person = $people->where('identifier', $identifier)->first();

            // That didn't work? Maybe they are a new team member
            if (!$person) {
                $person = $people->where('identifier', 'like', "r:%:{$teamYear}")->first();
                if ($person) {
                    $person->identifier = $identifier;
                    $person->save();
                }
            }
        }

        // If there was only one, or if we couldn't find them by now, just grab the first one
        if (!$person && !$people->isEmpty()) {
            $person = $people->first();
        }

        return [$person, $identifier];
    }

    public static function firstOrNew(array $attributes)
    {
        $center = Center::find($attributes['center_id']);

        $incomingQuarter = Quarter::findForCenter($attributes['incoming_quarter_id'], $center);
        list($person, $identifier) = static::findAppropriatePerson(
            $center, $attributes['first_name'], $attributes['last_name'],
            $attributes['team_year'], $incomingQuarter
        );

        // If we couldnt find anyone with that name, create a new person
        if (!$person) {
            // Still haven't found them? Fine, create a new one then
            $person = Person::create([
                'center_id' => $attributes['center_id'],
                'first_name' => $attributes['first_name'],
                'last_name' => $attributes['last_name'],
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

        // TODO: FIXME
        // No idea why I have to do this, but it was breaking because parent::firstOfNew
        // was actually re-calling this method
        $attributes = [
            'team_year' => $attributes['team_year'],
            'incoming_quarter_id' => $attributes['incoming_quarter_id'],
            'person_id' => $person->id,
        ];

        if (!is_null($instance = (new static())->newQueryWithoutScopes()->where($attributes)->first())) {
            return $instance;
        }

        return new static($attributes);
        //return parent::firstOrNew([
        //    'team_year'           => $attributes['team_year'],
        //    'incoming_quarter_id' => $attributes['incoming_quarter_id'],
        //    'person_id'           => $person->id,
        //]);
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
