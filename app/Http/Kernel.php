<?php

namespace Sponsor\Http;

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
        \Sponsor\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'team.inject' => \Sponsor\Http\Middleware\InjectAdminDriver::class,
        'request.sign' => \Sponsor\Http\Middleware\CheckRequestSignature::class,
        'team.auth' => \Sponsor\Http\Middleware\TeamAuthenticate::class,
        'auth' => \Sponsor\Http\Middleware\Authenticate::class,
        'csrf' => \Sponsor\Http\Middleware\VerifyCsrfToken::class,
        'guest' => \Sponsor\Http\Middleware\RedirectIfAuthenticated::class,
    ];
}
