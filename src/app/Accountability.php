<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class Accountability extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        'name',
        'context',
        'display',
    );

    protected $hidden = ['updated_at', 'created_at'];

    public function scopeName($query, $name)
    {
        return $query->whereName($name);
    }

    public function scopeContext($query, $context)
    {
        return $query->whereContext($context);
    }

    /**
     * Remove anyone who is accountable for a given accountability in a given center.
     *
     * If the 'except' parameter is given, do not remove accountability from the given person ID.
     *
     * @param  int $accountabilityId Which accountability to remove.
     * @param  int $centerId         Which center to use.
     * @param  Carbon $asOf          The effective date of the removal.
     * @param  int $except           If provided, do not remove this given person ID
     */
    public static function removeAccountabilityFromCenter(int $accountabilityId, int $centerId, Carbon $asOf, $except = null): int
    {
        // NOTE: this used to be an UPDATE... INNER JOIN which used to be able to do this change
        // in only one query instead of potentially 1+N queries, but this was not a query
        // which sqlite could handle, so it's been refactored to two queries.

        // Phase 1: select unique persons with accountability in center.
        $query = AccountabilityMapping::where('accountability_id', $accountabilityId)
            ->where('center_id', $centerId)
            ->activeOn($asOf);

        if ($except !== null) {
            $query = $query->where('person_id', '!=', $except);
        }
        $results = $query->get();

        // Phase 2: Actually end these accountabilities.
        foreach ($results as $ap) {
            if ($asOf->eq($ap->starts_at)) {
                $ap->delete();
            } else {
                $ap->ends_at = $asOf;
                $ap->save();
            }
        }

        return count($results);
    }
}
