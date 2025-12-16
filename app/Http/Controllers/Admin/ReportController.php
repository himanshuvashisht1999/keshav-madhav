<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\{
    ReportService as Service
};

class ReportController extends Controller
{
    protected $service;
    public function __construct(
        Service $service, 
    ) {
        $this->service = $service;
    }

    public function salesOrder(Request $request)
    {
        $response['data'] = $this->service->salesOrder($request);
        return view('admin.report.sales_order',$response);
    }

}
