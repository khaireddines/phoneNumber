<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->bearerToken() != env('API_TOKEN'))
            return response(['success'=>false,'message'=>'Unauthorized'],401);
        return $next($request);
    }
}
