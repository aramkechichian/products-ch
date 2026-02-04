<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, always return null (will throw JSON exception)
        if ($request->is('api/*')) {
            return null;
        }

        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        // For API requests, let the parent method throw AuthenticationException
        // which will be handled by the Exception Handler
        parent::unauthenticated($request, $guards);
    }
}
