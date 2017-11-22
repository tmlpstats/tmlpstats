<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();

        $this->call('RegionsTableSeeder');
        $this->call('CentersTableSeeder');
        $this->call('QuartersTableSeeder');
        $this->call('RegionQuarterDetailsTableSeeder');
        $this->call('RolesTableSeeder');
        $this->call('AccountabilitiesTableSeeder');

        // Seeders needed for Scoreboard tests
        $this->call('ScoreboardStatsReportTableSeeder');
        $this->call('ScoreboardCenterStatsDataTableSeeder');
        $this->call('ScoreboardGlobalReportStatsReportTableSeeder');
        $this->call('ScoreboardGlobalReportTableSeeder');

        // Import people and people related objects
        $this->call('PeopleObjectSeeder');

        if (env('APP_ENV') === 'local') {
            $this->call('DefaultAdminSeeder');
        }

        Model::reguard();
    }
}
