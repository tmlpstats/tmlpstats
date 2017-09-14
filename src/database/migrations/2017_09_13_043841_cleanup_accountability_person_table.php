<?php
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class CleanupAccountabilityPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        ///////////////////////
        //// PHASE 1: Select accountability_person annotated with center_id
        $allAps = DB::table('accountability_person')
            ->leftJoin('people', 'people.id', '=', 'accountability_person.person_id')
            ->select('people.center_id', 'accountability_person.*')
            ->orderBy('person_id')->orderBy('accountability_id')->orderBy('starts_at');

        // Group by person and accountability.
        $byPersonAccountability = [];
        foreach ($allAps->get() as $oldAp) {
            $oldAp->starts_at = Carbon::parse($oldAp->starts_at);
            // Fix: ends_at to a larger value.
            $oldAp->ends_at = ($oldAp->ends_at != null) ? Carbon::parse($oldAp->ends_at) : Carbon::parse('2019-01-01');
            $key = "{$oldAp->person_id}:{$oldAp->accountability_id}";
            $byPersonAccountability[$key][] = $oldAp;
        }

        $TO_COPY = ['person_id', 'accountability_id', 'center_id', 'starts_at', 'ends_at', 'created_at', 'updated_at'];

        ///////////////////////
        //// PHASE 2: Take the by person+accountability keyed
        foreach ($byPersonAccountability as $k => $instances) {
            $seen = [];
            foreach ($instances as $oldAp) {
                $key = "{$oldAp->starts_at}";
                if (!isset($seen[$key])) {
                    $seen[$key] = $oldAp;
                    continue;
                }
                $existing = $seen[$key];

                if ($existing->ends_at->lt($oldAp->ends_at)) {
                    $seen[$key] = $oldAp;
                }
            }

            $sValues = array_values($seen);
            echo "Person $k : From " . count($instances) . ' To ' . count($sValues) . "\n";
            $toInsert = collect($sValues)
                ->values()
                ->map(function ($x) use ($TO_COPY) {
                    $output = [];
                    foreach ($TO_COPY as $key) {
                        $output[$key] = $x->$key;
                    }

                    return $output;
                })
                ->all();
            DB::table('accountability_mappings')->insert($toInsert);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*
        Schema::table('accountability_person', function (Blueprint $table) {
            $table->dropForeign(['center_id']);
            $table->dropIndex(['starts_at']);
            $table->dropUnique('idx_ap_person_starts');
            $table->dropIndex('idx_ap_center_accountabilities');
        });*/
    }
}
