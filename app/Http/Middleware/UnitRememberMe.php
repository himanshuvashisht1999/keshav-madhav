<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class UnitRememberMe
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('unit_auth') && $request->hasCookie('unit_remember')) {
            try {
                $authData = json_decode(decrypt($request->cookie('unit_remember')), true);
                if ($authData) {
                    session(['unit_auth' => $authData]);
                }
            } catch (\Exception $e) {
                // Ignore error if cookie is invalid
            }
        }

        return $next($request);
    }
}
