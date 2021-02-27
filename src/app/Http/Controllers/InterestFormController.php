<?php

namespace TmlpStats\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use TmlpStats\Http\Requests;
use TmlpStats\InterestForm;

class InterestFormController extends Controller
{

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('partials.forms.interestform');
    }

    public function submit(Request $request)
    {
        $interest_form = new InterestForm();
        $interest_form->firstname = $request->input('firstname');
        $interest_form->lastname = $request->input('lastname');
        $interest_form->email = $request->input('email');
        $interest_form->phone = $request->input('phone');
        $interest_form->team_id = $request->input('team');

        $team_interest = $request->input('team_interest');
        $interest_form->regional_statistician_team = $team_interest === 'regional' || $team_interest === 'both';
        $interest_form->vision_team = $team_interest === 'vision' || $team_interest === 'both';

        $interest_form->save();

        $this->sendInterest($interest_form);

        return redirect('/');
    }

    /**
     * Send the invitation email
     *
     * @param Invite $invite
     */
    protected function sendInterest(InterestForm $interest_form)
    {
        try {
            Mail::send('emails.interestform.notification', compact('interest_form'),
                function ($message) use ($interest_form) {
                    // Only send email to person in production

                    if (config('app.env') === 'prod') {
                        $message->from('no-reply@tmlpstats.com', 'TMLPSTATS');
                        $message->replyTo('visiontmlp@googlegroups.com', 'The Vision Team');
                        if ($interest_form->vision_team) {
                            $message->to('visiontmlp@googlegroups.com');
                        } else {
                            $message->bcc('visiontmlp@googlegroups.com');
                        }

                        if ($interest_form->regional_statistician_team) {
                            $region = $interest_form->team->getGlobalRegion();
                            $message->to($region->email, $region->name);
                        }
                        $message->cc('global.statistician@gmail.com');

                    } else {

                        if ($interest_form->vision_team) {
                            $message->to('vision.tmlp@gmail.com');
                        } else {
                            $message->bcc('vision.tmlp@gmail.com');
                        }

                        if ($interest_form->regional_statistician_team) {
                            $region = $interest_form->team->getGlobalRegion();
                            $message->to('nicholas.tmlp@gmail.com', $region->name);
                        }

                    }

                    $team = ($interest_form->vision_team ? "Vision" : "") .
                        (($interest_form->vision_team && $interest_form->regional_statistician_team) ? " and " : ($interest_form->regional_statistician_team ? "" :  "Team")) .
                        ($interest_form->regional_statistician_team ? " Regional Statistician Team" : "") .
                        ($interest_form->vision_team && $interest_form->regional_statistician_team ? "s" : "");
                    $message->subject("New interest in the " . $team);
                });

            $successMessage = "Success! interest email sent.";
            if (config('app.env') === 'prod') {
                Log::info($successMessage);
            } else {
                Log::info($successMessage);
                $successMessage .= "<br/><br/><strong>Since this is development, we sent it to " . config('tmlp.admin_email') . " instead.</strong>";
            }
            $results['success'][] = $successMessage;

        } catch (\Exception $e) {
            Log::error("Exception caught sending invite email: " . $e->getMessage());
            $results['error'][] = "Failed to send interest email. Please try again.";
        }

        if ($interest_form->vision_team) {
            try {
                Mail::send('emails.interestform.response', compact('interest_form'),
                    function ($message) use ($interest_form) {
                        // Only send email to person in production

                        $message->from('no-reply@tmlpstats.com', 'Vision Team');
                        $message->replyTo('visiontmlp@googlegroups.com', 'Vision Team');
                        $message->to($interest_form->email);
                        $message->bcc('visiontmlp@googlegroups.com');
                        $message->subject("Thank you for your interest in the Vision Team");
                    });

                $successMessage = "Success! interest email sent.";
                Log::info($successMessage);
            } catch (\Exception $e) {
                Log::error("Exception caught sending invite email: " . $e->getMessage());
                $results['error'][] = "Failed to send interest email. Please try again.";
            }
        }



        return $results;
    }

}
