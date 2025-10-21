<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // For API requests, don't redirect - just return null to trigger a 401 response
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }
        
        // For web routes that need authentication, you would define a login route
        // For now, just return null to avoid the route error
        return null;
    }
}
