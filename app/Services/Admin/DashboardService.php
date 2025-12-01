<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Fabric;
use App\Models\ItemAttributeValue;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
class DashboardService {
    
    public function index(Request $request){
            return true; 
    }

    public function fabricStock(Request $request){
        $filter = $request->query('filter', 'all');
        $dates = $this->getFilter($filter); // returns ['start' => 'YYYY-MM-DD HH:MM:SS', 'end' => 'YYYY-MM-DD HH:MM:SS']

        $query = Fabric::withSum('stocks as total_meter', 'meter');

        // Apply date range filter if start and end are set
        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('created_at', [$dates['start'], $dates['end']]);
        }

        // Get results
        $queue = $query->get();

        // Extract labels and data
        $labels = $queue->pluck('sku')->toArray();
        $data = $queue->pluck('total_meter')->toArray();

        // Prepare chart-ready array
        $chartData = [
            'labels' => $labels,
            'data'   => $data
        ];

        // Return JSON
        return response()->json($chartData);
    }

     public function itemStock(Request $request){
        $filter = $request->query('filter', 'all');
        $dates = $this->getFilter($filter); // returns ['start' => 'YYYY-MM-DD HH:MM:SS', 'end' => 'YYYY-MM-DD HH:MM:SS']

        $query = ItemAttributeValue::withSum('item_stocks as total_quantity', 'quantity');

        // Apply date range filter if start and end are set
        if ($dates['start'] && $dates['end']) {
            $query->whereBetween('created_at', [$dates['start'], $dates['end']]);
        }

        // Get results
        $queue = $query->get();

        // Extract labels and data
        $labels = $queue->pluck('sku')->toArray();
        $data = $queue->pluck('total_quantity')->toArray();

        // Prepare chart-ready array
        $chartData = [
            'labels' => $labels,
            'data'   => $data
        ];

        // Return JSON
        return response()->json($chartData);
    }


    public function getFilter($filter){
        
        $startDate = null;
        $endDate = null;
        switch($filter) {
            case 'today':
                $startDate = Carbon::today()->startOfDay()->format('Y-m-d H:i:s'); // 00:00:00 today
                $endDate   = Carbon::today()->endOfDay()->format('Y-m-d H:i:s');   // 23:59:59 today
                break;

            case 'yesterday':
                $startDate = Carbon::yesterday()->startOfDay()->format('Y-m-d H:i:s');
                $endDate   = Carbon::yesterday()->endOfDay()->format('Y-m-d H:i:s');
                break;

            case 'week': // current week (Mon - Sun)
                $startDate = Carbon::now()->startOfWeek()->format('Y-m-d H:i:s');
                $endDate   = Carbon::now()->endOfWeek()->format('Y-m-d H:i:s');
                break;

            case 'month': // current month
                $startDate = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
                $endDate   = Carbon::now()->endOfMonth()->format('Y-m-d H:i:s');
                break;

            case 'year': // current year
                $startDate = Carbon::now()->startOfYear()->format('Y-m-d H:i:s');
                $endDate   = Carbon::now()->endOfYear()->format('Y-m-d H:i:s');
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