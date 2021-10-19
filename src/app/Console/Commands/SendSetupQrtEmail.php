<?php

namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use DB;
use TmlpStats;

class SendSetupQrtEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:setup-qrt {qrt? : The id of the quarter. Defaults to the last quarter created.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends an email to the regional statisticians to notify them to setup the dates for the new quarter';

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
        $qrtId = $this->argument('qrt');

        if (!$qrtId) {
            $qrt = DB::table('quarters')->orderBy('year', 'desc')->orderBy('quarter_number', 'desc')->first();
            $qrtId = $qrt->id;
        }

        $this->send_set_quarter_dates_email($qrtId);

    }

    public function send_set_quarter_dates_email($quarter_id) {


        $quarter = TmlpStats\Quarter::find($quarter_id);
        $regions = TmlpStats\Region::all();

        foreach ($regions as $region) {
            if ($region->isGlobalRegion()) {
                // use email associated to region other that NA because regional statistician has a different email address
                $to = $region->id !== 1 ? $region->email : 'na.statistician@gmail.com';
                try {
                    Mail::send('emails.set_new_quarter_dates', ['region' => $region, 'quarter' => $quarter ], function ($message) use ($quarter, $region, $to) {
                        $message->to($to, $region->name . ' Statistician');
                        $message->cc(['global.statistician@gmail.com' => 'Global Statistician', 'vision.tmlp@gmail.com' => 'Vision Leader']);
                        $message->replyTo('vision.tmlp@gmail.com');
                        $message->subject("[Action Required] Set New Quarter Dates for {$quarter->getDisplayLabel()}");
                    });
                    $this->info("Sent test email to {$to}");
                } catch (\Exception $e) {
                    $this->info('Caught exception sending error email: ' . $e->getMessage());
                }
            }
        }

    }
}
