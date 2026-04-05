<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\CompanyCapital;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentDashboardController extends Controller
{
    public function index()
    {
        return view('admin.payment.dashboard');
    }

    public function getData(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $query = Payment::query();

        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
            $filter = 'custom'; // Override predefined filter
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
            $filter = 'custom';
        }

        if ($filter !== 'all' && $filter !== 'custom') {
            switch ($filter) {
                case 'today':
                    $query->whereDate('payment_date', Carbon::today());
                    break;
                case 'yesterday':
                    $query->whereDate('payment_date', Carbon::yesterday());
                    break;
                case 'week':
                    $query->whereBetween('payment_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('payment_date', Carbon::now()->month)->whereYear('payment_date', Carbon::now()->year);
                    break;
                case 'year':
                    $query->whereYear('payment_date', Carbon::now()->year);
                    break;
            }
        }

        $payments = $query->get();

        // Query Company Capital with same filters
        $capQuery = CompanyCapital::query();
        if ($request->has('date_from') && $request->date_from) {
            $capQuery->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $capQuery->whereDate('transaction_date', '<=', $request->date_to);
        }
        if ($filter !== 'all' && $filter !== 'custom') {
            switch ($filter) {
                case 'today':
                    $capQuery->whereDate('transaction_date', Carbon::today());
                    break;
                case 'yesterday':
                    $capQuery->whereDate('transaction_date', Carbon::yesterday());
                    break;
                case 'week':
                    $capQuery->whereBetween('transaction_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $capQuery->whereMonth('transaction_date', Carbon::now()->month)->whereYear('transaction_date', Carbon::now()->year);
                    break;
                case 'year':
                    $capQuery->whereYear('transaction_date', Carbon::now()->year);
                    break;
            }
        }
        $capitals = $capQuery->get();

        // Summary Data
        $totalReceived = $payments->where('payment_type', 'received')->sum('amount') + $capitals->sum('amount');
        $totalPaid = $payments->where('payment_type', 'paid')->sum('amount');
        $balance = $totalReceived - $totalPaid;

        // Category Breakdown
        $categoryData = $payments->groupBy('payment_category')->map(function ($group) {
            return [
                'received' => $group->where('payment_type', 'received')->sum('amount'),
                'paid' => $group->where('payment_type', 'paid')->sum('amount')
            ];
        });

        if ($capitals->isNotEmpty()) {
            $categoryData['capital_addition'] = [
                'received' => $capitals->sum('amount'),
                'paid' => 0
            ];
        }

        // Monthly Trend (Last 6 Months)
        $trendData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $monthPayments = Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->get();
            $monthCapitals = CompanyCapital::whereBetween('transaction_date', [$monthStart, $monthEnd])->get();

            $trendData[] = [
                'month' => $month->format('M Y'),
                'received' => $monthPayments->where('payment_type', 'received')->sum('amount') + $monthCapitals->sum('amount'),
                'paid' => $monthPayments->where('payment_type', 'paid')->sum('amount')
            ];
        }

        return response()->json([
            'summary' => [
                'total_received' => number_format($totalReceived, 2),
                'total_paid' => number_format($totalPaid, 2),
                'balance' => number_format($balance, 2),
                'balance_raw' => $balance
            ],
            'categories' => [
                'labels' => $categoryData->keys()->map(fn($c) => ucwords(str_replace('_', ' ', $c))),
                'received' => $categoryData->pluck('received')->values(),
                'paid' => $categoryData->pluck('paid')->values(),
            ],
            'trend' => [
                'labels' => collect($trendData)->pluck('month'),
                'received' => collect($trendData)->pluck('received'),
                'paid' => collect($trendData)->pluck('paid'),
            ]
        ]);
    }
}
