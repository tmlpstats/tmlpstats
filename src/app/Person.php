<?php
namespace TmlpStats;

use Carbon\Carbon;
use DB;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
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
        if ($name == 'accountabilities') {
            return $this->getAccountabilities();
        } else {
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

    /**
     * Get a list of the accountabilities person currently holds
     *
     * @param Carbon $when  Reference date/time for getting accountability.
     * @return array
     */
    public function getAccountabilities(Carbon $when = null)
    {
        if ($when == null) {
            $when = Util::now();
        }

        $allAccountabilities = $this->accountabilities()->get();

        $accountabilities = [];
        foreach ($allAccountabilities as $myAccountability) {
            $startsAt = $myAccountability->pivot->starts_at
                ? Carbon::createFromFormat('Y-m-d H:i:s', $myAccountability->pivot->starts_at)
                : null;

            $endsAt = $myAccountability->pivot->ends_at
                ? Carbon::createFromFormat('Y-m-d H:i:s', $myAccountability->pivot->ends_at)
                : null;

            if ($startsAt && $startsAt->lte($when) && ($endsAt === null || $endsAt->gt($when))) {
                $accountabilities[] = $myAccountability;
            }
        }

        return $accountabilities;
    }

    /**
     * Get just the IDs of associated accountabilities. Many of the times we don't need ORM models anyway.
     * @param  Carbon|null $when [description]
     * @return [type]            [description]
     */
    public function getAccountabilityIds(Carbon $when = null)
    {
        if ($when == null) {
            $when = Util::now();
        }

        $items = DB::table('accountability_person')
            ->where('person_id', $this->id)
            ->where('starts_at', '<=', $when)
            ->where(function ($query) use ($when) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', $when);
            })->lists('accountability_id');

        return $items;
    }

    /**
     * Does person hold provided accountability
     *
     * @param Accountability $accountability
     *
     * @return bool
     */
    public function hasAccountability(Accountability $accountability)
    {
        $accountabilities = $this->getAccountabilities();
        foreach ($accountabilities as $myAccountability) {
            if ($myAccountability->id == $accountability->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add accountability for person
     *
     * @param Accountability $accountability
     * @param Carbon|null    $starts
     * @param Carbon|null    $ends
     */
    public function addAccountability(Accountability $accountability, Carbon $starts = null, Carbon $ends = null)
    {
        if (!$this->hasAccountability($accountability)) {
            $starts = $starts ?: Util::now();
            $this->accountabilities()->attach($accountability->id, ['starts_at' => $starts, 'ends_at' => $ends]);
        }
    }

    /**
     * Remove accountability from person
     *
     * @param Accountability $accountability
     */
    public function removeAccountability(Accountability $accountability)
    {
        if ($this->hasAccountability($accountability)) {
            DB::table('accountability_person')
                ->where('person_id', $this->id)
                ->where('accountability_id', $accountability->id)
                ->update(['ends_at' => Util::now()->copy()->subSecond()]);
        }
    }

    /**
     * Get the Region where the person's center is located
     *
     * @return null|Center
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
        // TODO: This handles the standard 10 digit North American phone number. Update to handle international formats
        if (isset($this->phone) && preg_match('/^(\d\d\d)[\s\.\-]?(\d\d\d)[\s\.\-]?(\d\d\d\d)$/', $this->phone, $matches)) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }

        return $this->phone;
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

    public function scopeByAccountability($query, $accountability)
    {
        return $query->whereHas('accountabilities', function ($query) use ($accountability) {
            $query->whereName($accountability->name)
                  ->where('starts_at', '<=', Util::now())
                  ->where(function ($query) {
                      $query->where('ends_at', '>', Util::now())
                            ->orWhereNull('ends_at');
                  });
        });
    }

    public function scopeIdentifier($query, $identifier)
    {
        return $query->whereIdentifier($identifier);
    }

    public function accountabilities()
    {
        return $this->belongsToMany('TmlpStats\Accountability', 'accountability_person', 'person_id', 'accountability_id')
                    ->withPivot(['starts_at', 'ends_at'])
                    ->withTimestamps();
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
