<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class OwnerMiddleware
{
    /** Link filters that are lists (multi-selects). Every other filter takes one value. */
    private const LIST_FILTERS = ['warehouse_id', 'vendor_id', 'unit_id', 'design_no', 'order_ids', 'design_nos', 'lot_nos'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role_id == 3) {
            $this->cleanQuery($request);
            return $next($request);
        }
        return redirect(route('owner.login'))->withError('Please Login your Owner account');
    }

    /**
     * A list sent for a one-value filter (?search[]=x) used to give an error page: drop it.
     * List filters keep only plain values (warehouse_id[a][]=b is dropped the same way).
     */
    private function cleanQuery(Request $request): void
    {
        $lists = self::LIST_FILTERS;
        if ($request->routeIs('owner.lots')) {
            $lists[] = 'lot_no'; // a multi-select only on the Lots page; a text box everywhere else
        }
        $query = [];
        foreach ($request->query->all() as $key => $value) {
            if (!is_array($value)) {
                $query[$key] = $value;
            } elseif (in_array($key, $lists, true)) {
                $query[$key] = array_values(array_filter($value, 'is_scalar'));
            }
        }
        $request->query->replace($query);
    }
}
