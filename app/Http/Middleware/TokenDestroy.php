<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class TokenDestroy
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
        $hour_time = date("Y-m-d H:i:s", strtotime("-4 hour"));
        DB::table('personal_access_tokens')->where('last_used_at', '<=', $hour_time)->delete();
        return $next($request);
    }
}
