<?php

namespace TmlpStats\Console\Commands;

use DB;
use Faker;
use Illuminate\Console\Command;
use Schema;
use TmlpStats\Person;
use TmlpStats\ReportToken;
use TmlpStats\Setting;
use TmlpStats\User;
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
        $faker = Faker\Factory::create();

        $this->line('Clearing password resets...');
        if (Schema::hasTable('password_resets')) {
            DB::table('password_resets')->truncate();
        }

        $this->line('Clearing sessions...');
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->truncate();
        }

        $this->line('Clearing stashes...');
        if (Schema::hasTable('submission_data')) {
            DB::table('submission_data')->truncate();
            DB::table('submission_data_log')->truncate();
        }

        $this->line('Scrubbing users...');
        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));
        $newPassword = bcrypt('password');
        foreach ($users as $user) {

            if ($user->id == 0) {
                continue;
            }

            $user->password = $newPassword;
            $user->rememberToken = null;

            if (!preg_match('/\.tmlpstats@gmail\.com$/', $user->email) && !preg_match('/\.statistician@gmail\.com$/', $user->email)) {
                $user->email = "user_{$user->id}@tmlpstats.com";
            }

            $user->save();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->line('Scrubbing people...');
        $people = Person::all();
        $bar = $this->output->createProgressBar(count($people));
        foreach ($people as $person) {

            $person->phone = substr("5555555555{$person->id}", -10, 10);
            $person->email = "person_{$person->id}@tmlpstats.com";

            if (strlen($person->lastName) > 2) {
                $person->lastName = $person->lastName[0];
            }
            $person->firstName = $faker->firstName;

            $person->save();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

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
}
