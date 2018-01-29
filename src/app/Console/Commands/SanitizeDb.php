<?php

namespace TmlpStats\Console\Commands;

use Carbon\Carbon;
use DB;
use Faker;
use Illuminate\Console\Command;
use Schema;
use TmlpStats as Models;
use TmlpStats\ModelCache;
use TmlpStats\Person;
use TmlpStats\ReportToken;
use TmlpStats\Setting;
use TmlpStats\Util;

class SanitizeDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sanitize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sanitizes the database of user information';

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
        // NEVER IN PROD!
        if (config('app.env') !== 'local' && config('app.env') !== 'stage') {
            $this->line('Must be in local env... aborting!');

            return false;
        }
        \DB::connection()->disableQueryLog();

        $faker = Faker\Factory::create();

        $this->line('Clearing password resets...');
        if (Schema::hasTable('password_resets')) {
            DB::table('password_resets')->truncate();
        }

        $this->line('Clearing sessions...');
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->truncate();
        }

        $this->line('Clearing invites...');
        if (Schema::hasTable('invites')) {
            DB::table('invites')->truncate();
        }

        $this->line('Scrubbing users...');
        $users = Models\User::all();
        $bar = $this->output->createProgressBar(count($users));
        $newPassword = bcrypt('password');
        $noScrubName = collect([]);
        foreach ($users as $user) {

            if ($user->id == 0) {
                continue;
            }

            $user->password = $newPassword;
            $user->rememberToken = null;

            if (!preg_match('/\.tmlpstats@gmail\.com$/', $user->email) && !preg_match('/\.statistician@gmail\.com$/', $user->email)) {
                $user->email = "user_{$user->id}@tmlpstats.com";
            } else {
                $noScrubName[$user->personId] = true;
            }

            $user->save(['timestamps' => false]);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->line('Scrubbing people...');
        $people = Models\Person::all()->keyBy('id');

        $bar = $this->output->createProgressBar(count($people));
        foreach ($people as $person) {

            if ($person->phone) {
                $person->phone = substr("5555555555{$person->id}", -10, 10);
            }
            if ($person->email) {
                $person->email = "person_{$person->id}@tmlpstats.com";
            }
            if (!$noScrubName->get($person->id, false)) {
                if (strlen($person->lastName) > 2) {
                    $person->lastName = $person->lastName[0];
                }
                $person->firstName = $faker->firstName;
            }

            $person->save(['timestamps' => false]);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // collect some garbage to free up RAM, maybe.
        $noScrubName = null;
        $users = null;
        $this->cleanGarbage();

        $this->line('Scrubbing stashes...');
        if (Schema::hasTable('submission_data')) {
            DB::table('submission_data_log')->truncate();

            $fourMonths = Carbon::now()->subMonths(4);
            Models\SubmissionData::where('updated_at', '<', $fourMonths)->delete();
            $q = Models\SubmissionData::whereIn('stored_type', ['application', 'team_member', 'next_qtr_accountability']);
            $bar = $this->output->createProgressBar($q->count());
            $task = $this;
            $q->chunk(500, function ($items) use ($people, $task, $bar, $faker) {
                $task->cleanGarbage();

                // For each of the stashes we can sanitize, try to find an associated person.
                // If there's an associated person, then we copy over the sanitized info from that person.
                foreach ($items as $sData) {
                    $d = $sData->data;
                    $personId = $task->bestCasePerson($sData);
                    $person = ($personId) ? $people->get($personId, null) : null;

                    foreach (['firstName', 'lastName', 'email', 'phone'] as $k) {
                        if (array_key_exists($k, $d)) {
                            if ($person) {
                                $d[$k] = $person->$k;
                            } else if ($k == 'firstName') {
                                $d[$k] = $faker->firstName;
                            } else if ($k == 'lastName') {
                                $d[$k] = $faker->lastName {0};
                            } else {
                                $d[$k] = null;
                            }
                        }
                    }
                    $sData->data = $d;
                    $sData->save(['timestamps' => false]);
                    $bar->advance();
                }
            });
            $bar->finish();
        }
        $this->line('');

        $people = null;
        $this->cleanGarbage();

        $this->line('Scrubbing reportTokens...');
        $reportTokens = ReportToken::all();
        $bar = $this->output->createProgressBar(count($reportTokens));
        foreach ($reportTokens as $token) {
            $token->token = Util::getRandomString();

            $token->save();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $reportTokens = null;
        $this->cleanGarbage();

        $this->line('Scrubbing settings...');
        $settings = Setting::name('centerReportMailingList')->get();
        $bar = $this->output->createProgressBar(count($settings));
        foreach ($settings as $setting) {
            $emails = explode(',', $setting->value);

            $newEmailList = [];
            for ($i = 0; $i < count($emails); $i++) {
                $newEmailList[] = "email_{$i}@tmlpstats.com";
            }
            $setting->value = implode(',', $newEmailList);

            $setting->save();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->line('Done');
    }

    public function cleanGarbage()
    {
        ModelCache::create()->flush();
        gc_collect_cycles();
        \DB::connection()->disableQueryLog();
    }

    public function bestCasePerson($sData)
    {
        $ref = null;
        switch ($sData->storedType) {
            case 'team_member':
                $ref = DB::table('team_members')->where('id', intval($sData->storedId));
                break;
            case 'application':
                $ref = DB::table('tmlp_registrations')->where('id', intval($sData->storedId));
                break;
            case 'next_qtr_accountability':
                $d = $sData->data;
                if ($d['teamMember'] ?? null) {
                    $ref = DB::table('team_members')->where('id', $d['teamMember']);
                } else if ($d['application'] ?? null) {
                    $ref = DB::table('tmlp_registrations')->where('id', $d['application']);
                }
                break;
            default:
                throw new Exception('wot');
        }
        if ($ref) {
            $obj = $ref->value('person_id');

            return $obj ?? null;
        }

        return null;
    }
}
