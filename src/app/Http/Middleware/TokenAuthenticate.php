<?php
namespace TmlpStats\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Session;
use TmlpStats\ReportToken;
use TmlpStats\User;

class TokenAuthenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Authenticate user based on a valid bearer token
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('reportTokenId')) {

            if (!$this->auth->check()) {
                // New user without a previously authenticated session
                $reportToken = $this->getReportToken();
                $user = User::find(0);
                $user->setReportToken($reportToken);

                $this->auth->login($user);
                Session::set('homePath', $reportToken->getReportPath());
            } else if ($this->auth->user()->hasRole('readonly')) {
                // A readonly user that has already been authenticated
                $reportToken = $this->getReportToken();
                $this->auth->user()->setReportToken($reportToken);
                Session::set('homePath', $reportToken->getReportPath());
            }
        }

        return $next($request);
    }

    public function getReportToken()
    {
        $reportToken = ReportToken::find(Session::get('reportTokenId'));
        if (!$reportToken->isValid()) {
            abort(403);
        }
        return $reportToken;
    }
}
