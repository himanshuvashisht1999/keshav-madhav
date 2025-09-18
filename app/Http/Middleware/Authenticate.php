<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;

class Authenticate
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
        if(!$request->bearerToken()){
            return response(['Status' => 401, 'Unauthorized' => 'client failed to authenticate with the server']);
        }
        if(!auth('api')->user()){
            return response(['Status' => 401, 'Unauthorized' => 'client failed to authenticate with the server']);
        }
        return $next($request);
    }
}
