<?php

namespace TmlpStats\Console\Commands;

use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use TmlpStats\Accountability;
use TmlpStats\Person;
use TmlpStats\TeamMember;
use TmlpStats\TmlpRegistration;
use TmlpStats\User;

class MergeDuplicatePeople extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:merge-duplicates {id1} {id2} {id3?} {id4?} {id5?} {id6?} {id7?} {id8?} {id9?} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges duplicate people by id';

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
        $arguments = $this->argument();

        if (!$this->argument('id1')) {
            $this->line("Provide a list of person_id's to merge. The first id will be used to overwrite the others.");
            $this->line("Use --dry-run to see what would happen without taking the actions");
            return 0;
        }

        $dryRun = false;
        if ($this->option('dry-run')) {
            $dryRun = true;
            $this->line('Dry run selected. No data will be modified.');
        }

        $people = [];
        foreach ($arguments as $key => $value) {
            if (strpos($key, 'id') !== 0 || !$value) {
                continue;
            }

            $person = Person::find($value);
            if (!$person) {
                $this->line("Unable to find person with id {$value}");
                continue;
            }

            $people[] = $person;
        }

        $primary = array_shift($people);
        $this->line("Using primary person {$primary->id}: {$primary->firstName} {$primary->lastName}");

        $primaryAccountabilities = [];
        $mappings = DB::table('accountability_person')->where('person_id', $primary->id)->get();
        foreach ($mappings as $map) {
            $ends = isset($map->ends_at) ? Carbon::parse($map->ends_at) : null;
            $active = $ends ? $ends->gt(Carbon::now()) : true;

            $primaryAccountabilities[$map->accountability_id] = $active;
        }

        foreach ($people as $person) {
            $this->line("----");
            $this->line("Inspecting person {$person->id}: {$person->firstName} {$person->lastName}");

            if ($person->centerId && $primary->centerId && $person->centerId != $primary->centerId) {
                $this->line("Primary {$primary->id} is from {$primary->center->name}, but person {$person->id} is from {$person->center->name}. Skipping");
                continue;
            } else if ($person->centerId && !$primary->centerId) {
                $this->line("Primary {$primary->id} does not have a center, but person {$person->id} is from {$person->center->name}. Person will loose their center settings. Please correct. Skipping");
                continue;
            }

            $registrations = TmlpRegistration::byPerson($person)->get();
            if (!$registrations->isEmpty()) {
                foreach ($registrations as $registration) {
                    $this->line("Updating TmlpRegistration person_id from {$registration->person_id} to {$primary->id}");
                    if (!$dryRun) {
                        $registration->personId = $primary->id;
                        $registration->save();
                    }
                }
            } else {
                $this->line("No TmlpRegistrations to update.");
            }
            $this->line("");

            $members = TeamMember::byPerson($person)->get();
            if (!$members->isEmpty()) {
                foreach ($members as $member) {
                    $this->line("Updating TeamMember person_id from {$member->person_id} to {$primary->id}");
                    if (!$dryRun) {
                        $member->personId = $primary->id;
                        $member->save();
                    }
                }
            } else {
                $this->line("No TeamMembers to update.");
            }
            $this->line("");

            $users = User::byPerson($person)->get();
            if (!$users->isEmpty()) {
                foreach ($users as $user) {
                    $this->line("Updating User person_id from {$user->person_id} to {$primary->id}");
                    if (!$dryRun) {
                        $user->personId = $primary->id;
                        $user->save();
                    }
                }
            } else {
                $this->line("No Users to update.");
            }
            $this->line("");

            $accountabilitiesMappings = DB::table('accountability_person')->where('person_id', $person->id)->get();
            if ($accountabilitiesMappings) {
                foreach ($accountabilitiesMappings as $mapping) {
                    $accountability = Accountability::find($mapping->accountability_id);

                    $ends = isset($mapping->ends_at) ? Carbon::parse($mapping->ends_at) : null;
                    $active = $ends ? $ends->gt(Carbon::now()) : true;

                    // Only upgrade accountabilities
                    if (!isset($primaryAccountabilities[$accountability->id]) || ($active && !$primaryAccountabilities[$accountability->id])) {
                        $endsString = $ends ? $ends->toDateTimeString() : 'never';
                        $this->line("Adding accountability {$accountability->name} which expires {$endsString}");
                        if (!$dryRun) {
                            DB::table('accountability_person')
                                ->where('person_id', $person->id)
                                ->where('accountability_id', $accountability->id)
                                ->update(['person_id' => $primary->id]);
                        }
                        $primaryAccountabilities[$accountability->id] = true;
                    } else {
                        $this->line("Primary already has accountability {$accountability->name}. Deleting");
                        if (!$dryRun) {
                            DB::table('accountability_person')
                                ->where('person_id', $person->id)
                                ->where('accountability_id', $accountability->id)
                                ->delete();
                        }
                    }
                }
            } else {
                $this->line("No Accountabilities to add.");
            }
            $this->line("");

            if (!$primary->email && $person->email && $person->email != $primary->email) {
                $this->line("Adding email {$person->email}");
                if (!$dryRun) {
                    $primary->email = $person->email;
                }
            }

            if (!$primary->phone && $person->phone && $person->phone != $primary->phone) {
                $this->line("Adding phone {$person->phone}");
                if (!$dryRun) {
                    $primary->phone = $person->phone;
                }
            }
            if (!$dryRun && $primary->isDirty()) {
                $primary->save();
            }
            $this->line("");

            $this->line("Deleting person {$person->id}");
            if (!$dryRun) {
                $person->delete();
            }
        }
        $this->line("----");

        $this->line("Done");
    }
}
