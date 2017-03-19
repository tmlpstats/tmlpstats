<?php
namespace TmlpStats\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Session;
use TmlpStats as Models;
use TmlpStats\ReportToken;

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
     * Run token authenticator manually
     */
    public function authenticate()
    {
        if (Session::has('reportTokenId')) {

            if (!$this->auth->check() && !$this->auth->user()) {

                $reportToken = ReportToken::find(Session::get('reportTokenId'));
                if (!$reportToken || !$reportToken->isValid()) {
                    abort(403);
                }

                $this->auth->onceUsingId(0);
                $user = $this->auth->user();
                $user->setReportToken($reportToken);
                Auth::setUser($user);

                if (!Session::has('timezone')) {
                    $timezone = 'US/Eastern';
                    if ($reportToken->ownerType == Models\Region::class) {
                        //TODO region to timezone mapp
                    } else if (($report = $reportToken->getReport()) instanceof Models\StatsReport) {
                        $timezone = $report->center->timezone;
                    }
                    Session::set('timezone', $timezone);
                }
                Session::set('homePath', $reportToken->getReportPath());
            }
        }
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
        $this->authenticate();

        return $next($request);
    }
}
