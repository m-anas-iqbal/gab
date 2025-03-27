<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CustomApiAuth extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        if ($this->auth->guard('api')->check()) {
            return $next($request);
        }

        return response()->json(['errors' => 'Unauthorized'], 401);
    }
}
