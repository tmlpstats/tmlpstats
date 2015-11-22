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

        $identifier = "r:{$regDateString}:{$attributes['team_year']}";

        $people = Person::where('center_id', $attributes['center_id'])
            ->where('first_name', $attributes['first_name'])
            ->where('last_name', $attributes['last_name'])
            ->get();

        $person = null;
        if ($people->count() > 1) {
            $person = $people->where('identifier', $identifier)->first();

            if (!$person) {
                // Maybe they withdrew and changed reg dates
                $searchIdentifier = "r:%:{$attributes['team_year']}";
                $person = $people->where('identifier', 'like', $searchIdentifier)->first();
                if ($person) {
                    $person->identifier = $identifier;
                    $person->save();
                }
            }
        }

        // If there was only one, or if we couldn't find them by now, just grab the first one
        if (!$person && !$people->isEmpty()) {
            $person = $people->first();

            // Fix for when people withdraw to change quarters
            if ($people->count() == 1 && $person->identifier != $identifier) {
                $person->identifier = $identifier;
                $person->save();
            }
        }

        // If we couldnt find anyone with that name, create a new person
        if (!$person) {
            $person = Person::create([
                'center_id'  => $attributes['center_id'],
                'first_name' => $attributes['first_name'],
                'last_name'  => $attributes['last_name'],
                'identifier' => $identifier,
            ]);
        } else if ($person->identifier != $identifier) {

            // Only update it on the first week of the quarter. Otherwise, they should be done intentionally.
            $center = static::getFromCache('center', $attributes['center_id'], function () use ($attributes) {
                return Center::find($attributes['center_id']);
            });

            if (Quarter::isFirstWeek($center->region)) {
                $person->identifier = $identifier;
                $person->save();
            }
        }

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
