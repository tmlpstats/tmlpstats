<?php

namespace TmlpStats\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain;

class UpdatePromise extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:fix-promise {centerId} {game} {date} {new}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update promise value in all stashes and active reports';

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
        $centerId = $this->argument('centerId');
        $center = Models\Center::find($centerId);
        if (!$center) {
            $this->line("Unable to find center {$centerId}.");
            return false;
        }

        $gameStr = strtoupper($this->argument('game'));
        $game = strtolower($gameStr);
        if (!in_array($game, Domain\Scoreboard::GAME_KEYS)) {
            $this->line("{$game} is not a valid game.");
            return false;
        }

        try{
            $dateStr = $this->argument('date');
            $reportingDate = Carbon::parse($dateStr)->startOfDay();
        } catch(\Exception $e) {
            $this->line("{$dateStr} is not a valid date.");
            return false;
        }

        $new = $this->argument('new');
        if (!is_numeric($new)) {
            $this->line("New value '{$new}' is not a valid number.");
            return false;
        }

        $this->line("Updating {$gameStr} promise for {$center->name} on {$dateStr}");

        // Update official scoreboard
        $csds = Models\CenterStatsData::promise()
            ->byCenter($center)
            ->reportingDate($reportingDate)
            ->official()
            ->with('statsReport')
            ->get();

        if ($csds->isEmpty()) {
            $this->line("Unable to find promise for {$dateStr}.");
            return false;
        }

        $csd = $csds[0];
        if (count($csds) > 1) {
            foreach ($csds as $c) {
                if ($c->statsReport->reportingDate->gt($csd->statsReport->reportingDate)) {
                    $csd = $c;
                }
            }
        }

        if ($csd->$game == $new) {
            $this->line("Stats report value already set to {$new}.");
            return true;
        }

        // Get the original scoreboard for calculating points change
        $weeksReport = Models\StatsReport::byCenter($center)
            ->reportingDate($csd->reportingDate)
            ->official()
            ->first();

        $originalScoreboard = null;
        if ($weeksReport) {
            $originalScoreboard = App::make(Api\LocalReport::class)->getWeekScoreboard($weeksReport);
        }

        $this->line("Changing promise {$csd->id} made on stats report {$csd->statsReportId} from {$csd->reportingDate->toDateString()} from {$csd->$game} to {$new}");
        $csd->$game = $new;
        $csd->save();

        // Update stashes
        $stash = Models\SubmissionData::centerDate($center, $csd->reportingDate)
            ->typeId('scoreboard_week', $reportingDate->toDateTimeString())
            ->first();

        if ($stash) {
            $sb = Domain\Scoreboard::fromArray($stash->data);

            $stashedValue = $sb->game($game)->promise();
            if ($stashedValue != $new) {
                $this->line("Changing stash {$stash->id} from {$stashedValue} to {$new}");
                $sb->setValue($game, 'promise', $new);
                $stash->data = $sb->toNewArray();
                $stash->save();
            } else {
                $this->line("Stashed value already set to {$new}.");
            }
        } else {
            $this->line("No stashes found.");
        }

        // Did this impact the scoreboard points/rating?
        $newScoreboard = null;
        if ($weeksReport) {
            App::make(Api\Context::class)->clearEncapsulations(); // clear the cache
            $newScoreboard = App::make(Api\LocalReport::class)->getWeekScoreboard($weeksReport);
        }

        if (!$weeksReport) {
            $this->line("No report submitted for {$dateStr} so points were not affected.");
        } elseif ($newScoreboard->points() != $originalScoreboard->points()) {
            $ratingMsg = '';
            if ($newScoreboard->rating() != $originalScoreboard->rating()) {
                $ratingMsg = " and rating {$originalScoreboard->rating()} to {$newScoreboard->rating()}";
            }
            $this->line("New promise changed points from {$originalScoreboard->points()} to {$newScoreboard->points()}{$ratingMsg}.");
        } else {
            $this->line("New promise did not change the points or rating.");
        }
    }
}
