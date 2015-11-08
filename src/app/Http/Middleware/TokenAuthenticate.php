<?php
namespace TmlpStats\Http\Middleware;

use Illuminate\Contracts\Auth\Guard;
use TmlpStats\ReportToken;
use TmlpStats\User;

use Auth;
use Closure;
use Session;

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

                $reportToken = ReportToken::find(Session::get('reportTokenId'));
                if (!$reportToken->isValid()) {
                    abort(403);
                }

                $this->auth->onceUsingId(0);
                $user = $this->auth->user();
                $user->setReportToken($reportToken);
                Auth::setUser($user);

                Session::set('homePath', $reportToken->getReportPath());
            }
        }

        return $next($request);
    }


}
