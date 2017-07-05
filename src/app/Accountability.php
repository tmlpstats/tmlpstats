<?php
namespace TmlpStats;

use Carbon\Carbon;
use DB;
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

    public function people()
    {
        return $this->belongsToMany('TmlpStats\Person', 'accountability_person', 'accountability_id', 'person_id')
                    ->withPivot(['starts_at', 'ends_at'])
                    ->withTimestamps();
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
    public static function removeAccountabilityFromCenter($accountabilityId, $centerId, Carbon $asOf, $except = null)
    {
        // NOTE: this used to be an UPDATE... INNER JOIN which used to be able to do this change
        // in only one query instead of potentially 1+N queries, but this was not a query
        // which sqlite could handle, so it's been refactored to two queries.

        // Phase 1: select unique persons with accountability in center.
        $query = '
            SELECT person_id, accountability_id
            FROM accountability_person AS ap
            INNER JOIN people AS p ON p.id = ap.person_id
            WHERE
                ap.accountability_id = ?
                AND p.center_id = ?
                AND (ap.ends_at IS NULL OR ap.ends_at > ?)';
        $params = [$accountabilityId, $centerId, $asOf];

        if ($except !== null) {
            $query .= '   AND ap.person_id != ?';
            $params[] = $except;
        }

        $results = DB::select($query, $params);

        // Phase 2: Actually end these accountabilities.
        foreach ($results as $entry) {
            DB::update('
                UPDATE accountability_person SET ends_at = ?, updated_at = ?
                WHERE accountability_id = ?
                      AND person_id = ?
                      AND (ends_at IS NULL OR ends_at > ?)',
                [$asOf, Carbon::now(), $accountabilityId, $entry->person_id, $asOf]);

        }

        return count($results);
    }
}
