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

        if (!$request->get('message')) {
            return [
                'success' => false,
                'field' => 'message',
                'csrf_token' => csrf_token(),
                'message' => 'Please provide a message.',
            ];
        }

        if (!$request->get('topic')) {
            return [
                'success' => false,
                'field' => 'topic',
                'csrf_token' => csrf_token(),
                'message' => 'Please let us know how we can help you.',
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

        $feedback = $request->get('message');
        $topic = $request->get('topic');

        $ccList = [];
        if ($request->has('copySender')) {
            $ccList[] = $senderEmail;
        }

        $type = 'general';
        switch ($topic) {
            case 'I have a stats question':
                $type = 'Stats Question';
                $ccList[] = $sender->center->region->email();
                break;
            case 'I need technical help':
                $type = 'Technical Issue';
                break;
            case 'I have a suggestion':
                $type = 'Suggestion';
                break;
            case 'I have feedback':
            default:
                $type = 'Feedback';
                break;
        }

        $userAgent = $request->header('User-Agent');

        Mail::send('emails.feedback', compact('feedback', 'topic', 'sender', 'senderName', 'senderEmail', 'url', 'userAgent'),
            function($message) use ($type, $sender, $senderName, $senderEmail, $ccList) {

            $message->to(config('tmlp.admin_email'));
            $message->replyTo($senderEmail);
            $message->subject("{$type} Submitted - {$senderName} on " . Carbon::now()->toDateTimeString());

            if ($ccList) {
                foreach ($ccList as $email) {
                    $message->cc($email);
                }
            }
        });

        return [
            'success' => true,
            'message' => 'Thank you for your feedback! We really appreciate your teamwork in creating an awesome tool',
        ];
    }
}
