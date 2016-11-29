<?php
namespace TmlpStats\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Session;
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
    public function manualHandle()
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
        $this->manualHandle();

        return $next($request);
    }
}
