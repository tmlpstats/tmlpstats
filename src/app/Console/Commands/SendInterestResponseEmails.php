<?php

namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TmlpStats;

class SendInterestResponseEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:sendinterestresponse {email_addresses*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $email_addresses = $this->argument('email_addresses');

        $interest_forms = TmlpStats\InterestForm::inEmail($email_addresses)->get();

        foreach ($interest_forms as $interest_form) {
            if ($interest_form->vision_team) {
                try {
                    Mail::send(['text' => 'emails.interestform.responsetext'], compact('interest_form'),
                        function ($message) use ($interest_form) {
                            $message->from('no-reply@tmlpstats.com', 'Vision Team');
                            $message->to($interest_form->email);
                            $message->cc('visiontmlp@googlegroups.com');
                            $message->subject("Come Play With Us!");
                        }
                    );

                    $successMessage = "Success! interest email sent.";
                    Log::info($successMessage);
                } catch (\Exception $e) {
                    Log::error("Exception caught sending invite email: " . $e->getMessage());
                    $results['error'][] = "Failed to send interest email. Please try again.";
                }
            }
        }
    }
}
