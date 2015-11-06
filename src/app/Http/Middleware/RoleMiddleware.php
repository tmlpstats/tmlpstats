<?php

namespace TmlpStats\Http\Middleware;

use Closure;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if ($role === 'statistician') {
            if ($request->user()->roleId === null) {
                abort(403);
            }
        } else if (!$request->user()->hasRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}
