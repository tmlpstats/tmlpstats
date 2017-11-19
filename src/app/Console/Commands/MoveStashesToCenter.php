<?php

namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use TmlpStats as Models;

class MoveStashesToCenter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:move-stash {stashId} {toCenterId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move stashes to a specfific center. Used for re-activating a center.';

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
        $centerId = $this->argument('toCenterId');
        $toCenter = Models\Center::find($centerId);
        if (!$toCenter) {
            $this->line("Unable to find center {$centerId}.");
            return false;
        }

        $stashId = $this->argument('stashId');
        $stash = Models\SubmissionData::find($stashId);
        if (!$stash) {
            $this->line("Unable to find stash {$stashId}.");
            return false;
        }

        $fromCenter = Models\Center::find($stash->centerId);

        if ($fromCenter->id === $toCenter->id) {
            $this->line("Stash {$stash->id} is already part of {$toCenter->name}. Nothing to do...");
            return true;
        }

        $this->line("Moving stash {$stash->id} from {$fromCenter->name} to {$toCenter->name}.");

        // Also write to the logs so we have a record
        Log::notice("Moving stash {$stash->id} from {$fromCenter->name} to {$toCenter->name}.");

        $stash->centerId = $toCenter->id;
        if (isset($stash->data['center'])) {
            // Only update the data blob is it actually has a center parameter
            $data = $stash->data;
            $data['center'] = $toCenter->id;
            $stash->data = $data;
        }
        $stash->save();

        // Create log entries for to and from centers
        Models\SubmissionDataLog::create([
            'center_id' => $fromCenter->id,
            'reporting_date' => $stash->reportingDate,
            'stored_type' => $stash->storedType,
            'stored_id' => $stash->storedId,
            'data' => json_encode(['__moved' => ['from' => $fromCenter->id, 'to' => $toCenter->id]]),
            'user_id' => 1, // You can't know the user from the command line, so just use 1
        ]);
        Models\SubmissionDataLog::create([
            'center_id' => $toCenter->id,
            'reporting_date' => $stash->reportingDate,
            'stored_type' => $stash->storedType,
            'stored_id' => $stash->storedId,
            'data' => $stash->data,
            'user_id' => 1, // You can't know the user from the command line, so just use 1
        ]);

        if ($stash->storedId < 0 && empty($stash->data['_personId'])) {
            $this->line("New {$stash->storedType}. No additional changes needed.");
            return true;
        }

        // This is an existing object, move the underlaying model to new center
        $model = null;
        switch ($stash->storedType) {
            case 'course':
                $model = Models\Course::find($stash->storedId);
                break;
            case 'team_member':
                $member = Models\TeamMember::find($stash->storedId);
                if ($member && $member->person) {
                    $model = $member->person; // center is saved on the person
                } else if (!empty($stash->data['_personId'])) {
                    $model = Models\Person::find($stash->data['_personId']);
                }
                break;
            case 'application':
                $app = Models\TmlpRegistration::find($stash->storedId);
                if ($app && $app->person) {
                    $model = $app->person; // center is saved on the person
                } else if (!empty($stash->data['_personId'])) {
                    $model = Models\Person::find($stash->data['_personId']);
                }
                break;
            default:
                $this->line("Skipping {$stash->storedType} {$stash->storedId}.");
                break;
        }

        if (!$model) {
            $this->line("Could not find {$stash->storedType} {$stash->storedId} in db.");
            return true;
        }

        if ($model->centerId === $toCenter->id) {
            $this->line("{$stash->storedType} {$stash->storedId} is already part of {$toCenter->name}. Nothing to do...");
            return true;
        }

        $this->line("Moving {$stash->storedType} {$stash->storedId} from {$fromCenter->name} to {$toCenter->name}.");

        // Also write to the logs so we have a record
        Log::notice("Moving {$stash->storedType} {$stash->storedId} from {$fromCenter->name} to {$toCenter->name}.");

        $model->centerId = $toCenter->id;
        $model->save();

        return true;
    }
}
