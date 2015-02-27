<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call('QuarterTableSeeder');
		$this->call('CenterTableSeeder');
		$this->call('RoleTableSeeder');
		$this->call('AccountabilityTableSeeder');

		try {
			// Not checked in to github, and will not be available in prod
			$this->call('UserTableSeeder');

			// $this->call('CenterStatsDataTableSeeder');
			// $this->call('CenterStatsTableSeeder');
			// $this->call('TeamMemberTableSeeder');
			// $this->call('TeamMemberDataTableSeeder');
			// $this->call('CourseTableSeeder');
			// $this->call('CourseDataTableSeeder');
			// $this->call('TmlpGameTableSeeder');
			// $this->call('TmlpGameDataTableSeeder');
			// $this->call('ProgramTeamMemberTableSeeder');
			// $this->call('TmlpRegistrationTableSeeder');
			// $this->call('TmlpRegistrationDataTableSeeder');
			// $this->call('StatsReportTableSeeder');
		} catch (\ReflectionException $e) { }
	}
}
