<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\GeneralSettingsService as Service;
use App\Requests\Admin\GeneralSettingsUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Illuminate\Support\Facades\Hash;

class GeneralSettingsController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function edit(Request $request){
        $response['data'] = $this->service->getSingleData($request);
        return view('admin.general_settings',$response);
    }
    public function update(GeneralSettingsUpdateRequest $request){
        $data = $this->service->update($request); 
        return redirect()->back()->withSuccess('The settings have been successfully updated.');

    }
    

}