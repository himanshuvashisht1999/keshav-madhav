<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\StockService as Service;
use Illuminate\Support\Facades\Crypt;
use Auth;

class StockController extends Controller
{
    protected $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        return view('admin.stock.index');
    }
       public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function view(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.stock.view', $response);
    }
    public function detail(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.stock.detail', $response);
    }
}
