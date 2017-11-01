<?php
namespace TmlpStats\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        // Middleware\VerifyCsrfToken::class,
        \Fideloper\Proxy\TrustProxies::class,
//        Middleware\ActiveUsersMiddleware::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.token' => Middleware\TokenAuthenticate::class,
        'guest' => Middleware\RedirectIfAuthenticated::class,
        'role' => Middleware\RoleMiddleware::class,
    ];
}
