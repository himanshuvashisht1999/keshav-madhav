<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A sales agent the office has made inactive (or deleted) is signed out on the next page,
 * also when they were logged in before or use "remember me".
 */
class SalesAgentActive
{
    public function handle(Request $request, Closure $next)
    {
        $agent = Auth::guard('sales_agent')->user();
        if ($agent && (int) $agent->status !== 1) {
            Auth::guard('sales_agent')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $message = 'Your account is not active. Please contact the office.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 401);
            }
            return redirect()->route('agent.login')->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
