<?php

namespace App\Http\Middleware;

use Closure;

class AccountConfine
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
        return $next($request);
        if ($request->user()->type === 4) {
            return $next($request);
        } else {
            return response()->json(['error' => '无该页面操作权限！'],403);
        }
    }
}
