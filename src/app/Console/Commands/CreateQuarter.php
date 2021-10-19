<?php

namespace TmlpStats\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use TmlpStats;

use DB;
use TmlpStats\Quarter;
use TmlpStats\Region;

class CreateQuarter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:quarter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the next quarter in the database and send an email to the regional statistician to set the dates.';

    const QUARTER_DISTINCTIONS = [
            'Relatedness' => ['t1' => 'Relatedness', 't2' => 'Generosity', 'next_qrt' => 'Possibility'],
            'Possibility' => ['t1' => 'Possibility', 't2' => 'Integrity', 'next_qrt' => 'Opportunity'],
            'Opportunity' => ['t1' => 'Opportunity', 't2' => 'Listening', 'next_qrt' => 'Action'],
            'Action' => ['t1' => 'Action', 't2' => 'Responsibility', 'next_qrt' => 'Completion'],
            'Completion' => ['t1' => 'Completion', 't2' => 'Completion', 'next_qrt' => 'Relatedness']
        ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $quarter_id = $this->create_quarter();

        $this->update_current_quarter();

        $this->call('send:setup-qrt', [
            'qrt' => $quarter_id
        ]);

    }

    public function create_quarter()
    {
        $last_qrt = DB::table('quarters')->orderby('year', 'desc')->orderby('quarter_number', 'desc')->first();

        $last_qrt_distinctions = self::QUARTER_DISTINCTIONS[$last_qrt->t1_distinction];
        $new_qrt_distinctions = self::QUARTER_DISTINCTIONS[$last_qrt_distinctions['next_qrt']];

        $t1_distinction = $new_qrt_distinctions['t1'];
        $t2_distinction = $new_qrt_distinctions['t2'];
        $qrt_number = (($last_qrt->quarter_number) % 4) + 1;
        $year = $qrt_number == 1 ? $last_qrt->year + 1 : $last_qrt->year;
        $created_at = $updated_at = new \DateTime();

        $id = DB::table('quarters')->insertGetId(
            [
                't1_distinction' => $t1_distinction,
                't2_distinction' => $t2_distinction,
                'quarter_number' => $qrt_number,
                'year' => $year,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ]
        );

        $this->info("Created a new quarter - id:{$id}, t1:{$t1_distinction}, t2:{$t2_distinction}, qrt_num:{$qrt_number}, year:{$year}");

        return $id;
    }

    public function update_current_quarter() {

        $regions = Region::isGlobal()->get();

        $earliest_cl3 = Carbon::maxValue();
        $this_qrt = null;
        foreach ($regions as $region) {
            $qrt = Quarter::current($region)->first();
            $cl3_date = $qrt->getClassroom3Date($region->centers[0]);
            if ($cl3_date < $earliest_cl3) {
                $earliest_cl3 = $cl3_date;
                $this_qrt = $qrt;
            }
        }

        $this_qrt->next_quarter_created = true;

        $this_qrt->save();

    }



}
