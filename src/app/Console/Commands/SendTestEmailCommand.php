<?php
namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-test {to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $emailTo = $this->argument('to');

        try {
            Mail::send('emails.test', [], function ($message) use ($emailTo) {
                $message->to($emailTo);
                $message->replyTo('future.tmlpstats@gmail.com');
                $message->subject('Test email from TMLP Stats');
            });
            $this->info("Sent test email to {$emailTo}");
        } catch (\Exception $e) {
            $this->info('Caught exception sending error email: ' . $e->getMessage());
        }
    }
}
