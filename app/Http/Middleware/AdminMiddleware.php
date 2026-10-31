<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            // the owner login (role 3) opens the owner panel only, never the admin pages
            if ((int) Auth::guard('admin')->user()->role_id === 3) {
                return $request->expectsJson()
                    ? response()->json(['message' => 'The owner login opens the owner panel only.'], 403)
                    : redirect()->route('owner.dashboard')->with('error', 'The owner login opens the owner panel only.');
            }
            Auth::shouldUse('admin');
            return $next($request);
        }
        return redirect(route('admin.login'))->withError('Please Login your account');
    }
}
