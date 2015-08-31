<?php
namespace TmlpStats\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use Illuminate\Session\TokenMismatchException;

use Carbon\Carbon;

use App;
use Auth;
use Log;
use Mail;
use Redirect;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        // Ignore token expiration messages
        if (!($e instanceof TokenMismatchException) && !App::runningInConsole()) {

            $user = Auth::user()->email;
            $center = Auth::user()->center
                ? Auth::user()->center->name
                : 'unknown';
            $time = Carbon::now()->format('Y-m-d H:i:s');

            $body = "An exception was caught by '{$user}' from {$center} center at {$time} UTC: '" . $e->getMessage() . "'\n\n";
            $body .= $e->getTraceAsString() . "\n";
            try {
                Mail::raw($body, function($message) use ($center) {
                    $message->to(env('ADMIN_EMAIL'))->subject("Exception processing sheet for {$center} center in " . strtoupper(env('APP_ENV')));
                });
            } catch (Exception $e) {
                Log::error("Exception caught sending error email: " . $e->getMessage());
            }
        }
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($this->isHttpException($e))
        {
            return $this->renderHttpException($e);
        }
        else if ($e instanceof TokenMismatchException)
        {
            // Probably a session expiration. Redirect to login
            return redirect('auth/login')->with('message','Your session has expired. Please try logging in again.');
        }
        else
        {
            return parent::render($request, $e);
        }
    }

}
