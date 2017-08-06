<?php

namespace TmlpStats\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mail;
use TmlpStats\Http\Requests;

class ContactController extends Controller
{
    public function processFeedback(Request $request)
    {
        if (!Auth::check()) {
            abort(401);
        }

        if (!$request->has('message')) {
            return [
                'success' => false,
                'message' => 'Please provide a message.',
            ];
        }

        $sender = Auth::user();

        $senderName = "{$sender->firstName} {$sender->lastName}";
        if ($request->has('name')) {
            $senderName = $request->get('name');
        }

        $senderEmail = $sender->email;
        if ($request->has('email')) {
            $senderEmail = $request->get('email');
        }

        $url = 'not provided';
        if ($request->has('feedbackUrl')) {
            $url = $request->get('feedbackUrl');
        }

        $ccSender = $request->has('copySender');
        $feedback = $request->get('message');

        $userAgent = $request->header('User-Agent');

        Mail::send('emails.feedback', compact('feedback', 'sender', 'senderName', 'senderEmail', 'url', 'userAgent'),
            function($message) use ($sender, $senderName, $senderEmail, $ccSender) {

            $message->to(config('tmlp.admin_email'));
            $message->replyTo($senderEmail);
            $message->subject("Feedback Submitted - {$senderName} on " . Carbon::now()->toDateTimeString());

            if ($ccSender) {
                $message->cc($senderEmail);
            }
        });

        return [
            'success' => true,
            'message' => 'Thank you for your feedback! We really appreciate your teamwork in creating an awesome tool',
        ];
    }
}
