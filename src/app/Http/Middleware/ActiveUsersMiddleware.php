<?php

namespace TmlpStats\Http\Middleware;

use Auth;
use Cache;
use Carbon\Carbon;
use Closure;
use Route;

class ActiveUsersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // What is it that I want to accomplish?
        //   Know who's actively using the site
        //   Know when they last sent a request and to what endpoint
        //   Know if they are currently waiting on a response

        $user = Auth::user();
        $data = [];
        if ($user) {

            $data = Cache::tags('activeUsers')->get("activeUser{$user->id}");

            $data['previousRequests'][] = [
                'start' => $data['start'],
                'route' => $data['route'],
                'end'   => $data['end'],
            ];

            $data['start'] = Carbon::now()->format('U');
            $data['route'] = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];

            Cache::tags('activeUsers')->put("activeUser{$user->id}", $data, 120);

            // If an admin set this flag, the users if forcibly logged out.
            if (isset($data['forceLogout'])) {
                Auth::logout();
                return redirect()->guest('auth/login');
            }
        }

        $response = $next($request);

        if ($user) {
            $data['end'] = Carbon::now()->format('U');

            Cache::tags('activeUsers')->put("activeUser{$user->id}", $data, 120);
        }

        return $response;
    }
}


/*
* Performance monitor:
*   Keep track of memcache hits on reports
*   Keep track of how long it takes to validate reports
*   Remember reports that took a long time and alert me about it
*/
