<?php

namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountabilityMapping extends Model
{
    use CamelCaseModel;

    protected $table = 'accountability_mappings';
    protected $guarded = ['id'];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeByPerson($query, $person): Builder
    {
        if ($person instanceof Person) {
            $person = $person->id;
        }

        return $query->where('person_id', $person);
    }

    public function scopeActiveOn($query, Carbon $when): Builder
    {
        return $query
            ->where('starts_at', '<=', $when)
            ->where('ends_at', '>', $when);
    }

    public function scopeByAccountability($query, Accountability $accountability): Builder
    {
        return $query->where('accountability_id', $accountability->id);
    }

    public function scopeByCenter($query, $center): Builder
    {
        return $query->where('center_id', ($center instanceof Center) ? $center->id : $center);
    }

    public function accountability(): BelongsTo
    {
        return $this->belongsTo(Accountability::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public static function bulkSetCenterAccountabilities(Center $center, Carbon $startsAt, Carbon $endsAt, array $toApply)
    {
        $report = ['created' => 0, 'shortened' => 0, 'deleted' => 0, 'lengthened' => 0];
        $allExisting = static::byCenter($center)
            ->where('starts_at', '<', $endsAt) // notice endsAt and startsAt are swapped here
            ->where('ends_at', '>', $startsAt) // this is on purpose!
            ->whereIn('accountability_id', array_keys($toApply))
            ->get()
            ->groupBy('accountability_id');

        foreach ($toApply as $accId => $people) {
            $accExisting = collect($allExisting->get($accId))->groupBy('person_id');
            foreach ($people as $personId) {
                $existing = $accExisting->pull($personId); // pull is like get, but also removes the item.
                static::applyCenterPerson($center, $startsAt, $endsAt, $accId, $personId, $existing, $report);
            }

            // For any remaining people holding this accountability, curtail them (shorten)
            foreach ($accExisting->flatten(1) as $item) {
                if ($item->starts_at->lt($startsAt)) {
                    $item->ends_at = $startsAt;
                    $item->save();
                    $report['shortened']++;
                } else if ($item->ends_at->lte($endsAt)) {
                    // Fully enclosed within this time range, can delete.
                    $report['deleted']++;
                    $item->delete();
                } else {
                    $item->starts_at = $item->starts_at->max($endsAt);
                    $item->save();
                    $report['shortened']++;
                }
            }
        }

        return $report;
    }

    /**
     * This is mostly to move the rather deeply nested logic out for doing the actual apply of some updates to a center-accountability-person
     * @param  Center $center    The center, naturally.
     * @param  Carbon $startsAt  Starts At
     * @param  Carbon $endsAt    Ends At
     * @param  int    $accId     Accountability ID
     * @param  int    $personId  Person ID
     * @param  array|null $existing Anything existing to check.
     */
    protected static function applyCenterPerson(Center $center, Carbon $startsAt, Carbon $endsAt, int $accId, int $personId, $existing, &$report)
    {
        if ($existing && $existing->count()) {
            // TODO investigate what it would be like to do a 'gap filling' algorithm here.
            $threshold = $startsAt->copy()->subDay();
            $written = false;
            foreach ($existing as $acc) {
                if ($written) {
                    if ($acc->ends_at->gt($endsAt)) {
                        $acc->starts_at = $endsAt;
                        $acc->save();
                    }
                } else if ($acc->starts_at->lte($startsAt) && $acc->ends_at->gte($threshold)) {
                    $acc->ends_at = $acc->ends_at->max($endsAt);
                    $acc->save();
                    $report['shortened']++;
                    $written = true;
                } else if ($acc->ends_at->gt($endsAt)) {
                    $acc->starts_at = $acc->starts_at->min($startsAt);
                    $acc->save();
                    $report['lengthened']++;
                    $written = true;
                }
            }
            if (!$written) {
                throw new \Exception("When setting accountability {$accId} for person {$personId} with start {$startsAt} and end {$endsAt}, with existing {$existing}, Nothing written.");
            }
        } else {
            $report['created']++;
            // simplest scenario, no other entries exist, so add the accountability.
            static::create([
                'person_id' => $personId,
                'accountability_id' => $accId,
                'center_id' => $center->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ]);
        }

    }

}
