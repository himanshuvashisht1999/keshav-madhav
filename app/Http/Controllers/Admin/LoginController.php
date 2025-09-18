<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\LoginService as Service;
use App\Requests\Admin\LoginRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    //// Admin login page
    public function login(){
        if(Auth::guard('admin')->user()){
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }
    /// admin check login details
    public function postLogin(LoginRequest $request){

        $data = $this->service->postLogin($request); 
        
        if($data == 'success'){
            return redirect()->route('admin.dashboard')->withSuccess('You have successfully logged in.');
        }else{
            return redirect()->back()->withError('Invalid email or password. Please try again.');
        }
    }
    public function logout(){
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->withSuccess('You have successfully logged out.');
    }

}