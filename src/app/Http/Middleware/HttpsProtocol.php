<?php
namespace TmlpStats\Http\Middleware;

use Closure;

class HttpsProtocol
{
    public function handle($request, Closure $next)
    {
        if (!$request->secure() && config('app.env') !== 'local') {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
