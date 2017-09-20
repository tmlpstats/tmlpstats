<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use TmlpStats\Traits\CachedRelationships;

class Person extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'center_id',
        'identifier',
        'unsubscribed',
    ];

    protected $casts = [
        'unsubscribed' => 'boolean',
    ];

    public function __get($name)
    {
        switch ($name) {
            case 'fullName':
                return "{$this->firstName} {$this->lastName}";
            case 'shortName':
                return "{$this->firstName} {$this->lastName[0]}";
            default:
                return parent::__get($name);
        }
    }

    /**
     * Find a person by their first and last name, and center.
     *
     * This will do a best effort search for the person. It will try various combinations of
     * name tweaks until it has found someone.
     *
     * @param            $first
     * @param            $last
     * @param            $center
     * @param bool|false $replaced
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected static function findByName($first, $last, $center, $replaced = false)
    {
        $possibleMembers = self::firstName($first)
            ->lastName($last)
            ->byCenter($center)
            ->get();

        // If we didn't find anyone. Maybe they gave their first name here.
        // Try with just the first letter
        if ($possibleMembers->isEmpty() && strlen($last) > 1) {
            $possibleMembers = self::firstName($first)
                ->lastName($last[0]) // Just the first letter
                ->byCenter($center)
                ->get();
        }

        // If we still haven't found one, try some common character replacements
        // Careful not to get yourself into a loop
        if ($possibleMembers->isEmpty()) {
            $newFirst = '';
            if ($replaced !== '-' && strpos($first, '-') !== false) {
                $newFirst = str_replace('-', ' ', $first);
                $replaced = '-';
            } else if ($replaced !== '-' && strpos($first, ' ') !== false) {
                $newFirst = str_replace(' ', '-', $first);
                $replaced = '-';
            } else if ($replaced !== '.' && strpos($first, '.') !== false) {
                $newFirst = str_replace('.', '', $first);
                $replaced = '.';
            } else if ($replaced !== 'accent') {
                // Maybe there's an accent in the new one, but not in the existing one.
                $newFirst = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $first);
                $replaced = 'accent';
            }
            if ($newFirst) {
                $possibleMembers = static::findByName($newFirst, $last, $center, $replaced);
            }
        }

        return $possibleMembers;
    }

    protected function queryAccountabilityMappings(Carbon $when): Builder
    {
        return AccountabilityMapping::byPerson($this)
            ->activeOn($when);
    }

    /**
     * Get a list of the accountabilities person currently holds
     *
     * @param Carbon $when  Reference date/time for getting accountability.
     * @return array
     */
    public function getAccountabilities(Carbon $when): Collection
    {
        return $this->queryAccountabilityMappings($when)
                    ->with('accountability') // eager-load accountability
                    ->get() // execute query
                    ->pluck('accountability'); // get only the 'accountability' sub-object;
    }

    /**
     * Get just the IDs of associated accountabilities. Many of the times we don't need ORM models anyway.
     * @param  Carbon $when  Reference date/time for getting accountability.
     * @return array
     */
    public function getAccountabilityIds(Carbon $when): array
    {
        return $this->queryAccountabilityMappings($when)
                    ->pluck('accountability_id')
                    ->map(function ($x) {return intval($x);})
                    ->all();
    }

    /**
     * Does person hold provided accountability
     *
     * @param  Accountability $accountability
     * @param  Carbon         $when  Reference date/time for getting accountability.
     * @return boolean
     */
    public function hasAccountability(Accountability $accountability, Carbon $when): bool
    {
        $ap = $this->queryAccountabilityMappings($when)->byAccountability($accountability)->first();

        return ($ap !== null);
    }

    /**
     * Add accountability for person
     *
     * @param Accountability $accountability
     * @param Carbon         $starts
     * @param Carbon         $ends
     */
    public function addAccountability(Accountability $accountability, Carbon $starts, Carbon $ends)
    {
        if (!$this->hasAccountability($accountability, $starts)) {
            AccountabilityMapping::create([
                'person_id' => $this->id,
                'accountability_id' => $accountability->id,
                'center_id' => $this->centerId,
                'starts_at' => $starts,
                'ends_at' => $ends,
            ]);
        }
    }

    /**
     * Remove accountability from person
     * @deprecated Don't use this function!!!
     *
     * @param  Accountability $accountability
     * @param  Carbon         $when  Reference date/time for getting accountability.
     */
    public function removeAccountability(Accountability $accountability, Carbon $when)
    {
        if ($this->hasAccountability($accountability, $when)) {
            $ams = AccountabilityMapping::activeOn($when)
                ->person($this)
                ->accountability($accountability);
            foreach ($ams->get() as $am) {
                if ($am->starts_at->ne($when)) {
                    $am->ends_at = $when;
                    $am->save();
                }
            }
        }
    }

    /**
     * Get the Region where the person's center is located
     *
     * @return null|Region
     */
    public function homeRegion()
    {
        return $this->center ? $this->center->region : null;
    }

    /**
     * Get the user's formatted phone number
     * e.g.
     *      (555) 555-5555
     *
     * @return string|void
     */
    public function formatPhone()
    {
        return Util::formatPhone($this->phone);
    }

    public function scopeFirstName($query, $name)
    {
        return $query->whereFirstName($name);
    }

    public function scopeLastName($query, $name)
    {
        return $query->whereLastName($name);
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeIdentifier($query, $identifier)
    {
        return $query->whereIdentifier($identifier);
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function user()
    {
        return $this->hasOne('TmlpStats\User');
    }

    public function teamMember()
    {
        return $this->hasOne('TmlpStats\TeamMember');
    }

    public function registration()
    {
        return $this->hasOne('TmlpStats\TmlpRegistration', 'person_id');
    }
}
