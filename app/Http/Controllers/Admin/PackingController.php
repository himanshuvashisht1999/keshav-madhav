<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\PackingService as Service;
use Illuminate\Support\Facades\Crypt;
use Auth;

class PackingController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;

    }
    public function index(Request $request){
        $response['lot_numbers'] = $this->service->lot_numbers();
        dd($response['lot_numbers']);
        return view('admin.packing.index',$response);
    } 

}