<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array<int, string|null>  $guards
     * @return never
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        $redirectTo = null;

        if (! $request->expectsJson()) {
            $redirectTo = in_array('admin', $guards, true)
                ? route('login_admin')
                : route('login');
        }

        throw new AuthenticationException('Unauthenticated.', $guards, $redirectTo);
    }
}
