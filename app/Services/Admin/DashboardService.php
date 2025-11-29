<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Fabric;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
class DashboardService {
    
    public function index(Request $request){
            return true; 
    }

    public function fabricStock(Request $request){
        $filter = $request->query('filter', 'all'); // default to 'all' if not provided
        $dates = $this->getFilter($filter);
        dd($dates['start']);
        $queue = Fabric::withSum('stocks as total_meter', 'meter');
        dd($queue);
        $data = 1;
        return response()->json($data);
    }



    public function getFilter($filter){
        
        $startDate = null;
        $endDate = null;
        switch($filter) {
            case 'today':
                $startDate = Carbon::today()->startOfDay(); // 00:00:00 today
                $endDate   = Carbon::today()->endOfDay();   // 23:59:59 today
                break;

            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay();
                $endDate   = Carbon::yesterday()->endOfDay();
                break;

            case 'week': // current week (Mon - Sun)
                $startDate = Carbon::now()->startOfWeek();
                $endDate   = Carbon::now()->endOfWeek();
                break;

            case 'month': // current month
                $startDate = Carbon::now()->startOfMonth();
                $endDate   = Carbon::now()->endOfMonth();
                break;

            case 'year': // current year
                $startDate = Carbon::now()->startOfYear();
                $endDate   = Carbon::now()->endOfYear();
                break;

            case 'all': // no filter
            default:
                $startDate = null;
                $endDate   = null;
                break;
        }
        return ['start' => $startDate, 'end' => $endDate];
    }
    
}