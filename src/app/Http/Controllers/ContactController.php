<?php

namespace TmlpStats\Http\Controllers;

use Auth;
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
        $senderName = $request->has('name')
            ? $request->get('name')
            : "{$sender->firstName} {$sender->lastName}";
        $senderEmail = $request->has('email')
            ? $request->get('email')
            : $sender->email;

        $ccSender = $request->has('copySender');
        $feedback = $request->get('message');

        Mail::send('emails.feedback', compact('feedback', 'sender', 'senderName', 'senderEmail'),
            function($message) use ($sender, $senderEmail, $ccSender) {

            $message->to(env('ADMIN_EMAIL'));
            $message->replyTo($senderEmail);
            $message->subject('Feedback Submitted');

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
