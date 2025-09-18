<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
class LoginService {
    public function postLogin(Request $request){
        if(auth()->guard('admin')->attempt(['email' => $request->email,'role_id' => 1, 'password' => $request->password,'status' => 1])) {
            return 'success';
        }else{
            return 'fail';
        }
    }

}