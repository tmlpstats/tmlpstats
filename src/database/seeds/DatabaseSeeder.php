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

        $this->call('QuarterTableSeeder');
        $this->call('CenterTableSeeder');
        $this->call('RoleTableSeeder');
        $this->call('AccountabilityTableSeeder');

        try {
            $this->call('UserTableSeeder');
            $this->call('RoleUserTableSeeder');
            $this->call('CenterUserTableSeeder');

            $this->call('CenterStatsDataTableSeeder');
            $this->call('CenterStatsTableSeeder');
            $this->call('TeamMemberTableSeeder');
            $this->call('TeamMemberDataTableSeeder');
            $this->call('CourseTableSeeder');
            $this->call('CourseDataTableSeeder');
            $this->call('TmlpGameTableSeeder');
            $this->call('TmlpGameDataTableSeeder');
            $this->call('ProgramTeamMemberTableSeeder');
            $this->call('TmlpRegistrationTableSeeder');
            $this->call('TmlpRegistrationDataTableSeeder');
            $this->call('StatsReportTableSeeder');

            if (env('APP_ENV') === 'local') {
                $this->call('DefaultAdminSeeder');
            }
        } catch (\ReflectionException $e) {
            throw $e;
        }
    }
}

