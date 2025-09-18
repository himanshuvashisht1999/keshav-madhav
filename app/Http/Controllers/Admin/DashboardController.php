<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService as Service;
use App\Models\User;

class DashboardController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function dashboard(Request $request)
    {

        $year = $request->input('year') ?? date('Y');

        $total_users = User::whereYear('created_at', $year)->count();


        // Grouped data for chart
        $chartData = [
            'users' => User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                            ->whereYear('created_at', $year)
                            ->groupBy('month')->pluck('count', 'month')->toArray(),
        ];

        return view('admin.dashboard', compact(
            'total_users','year', 'chartData'
        ));
    }
}