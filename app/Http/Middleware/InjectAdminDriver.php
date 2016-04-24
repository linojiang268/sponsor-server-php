<?php
namespace Sponsor\Http\Middleware;

use Closure;
use Auth;

class InjectAdminDriver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Auth::setDefaultDriver('extended-eloquent-team');

        return $next($request);
    }
}
