<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\UserService as Service;
use App\Requests\Admin\UserStoreRequest;
use App\Requests\Admin\UserUpdateRequest;
use App\Requests\Admin\AdminProfileUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Illuminate\Support\Facades\Hash;
 
class UserController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.user.index');
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['branches'] = $this->service->branches();
        return view('admin.user.create',$response);
    }
    public function store(UserStoreRequest $request){
        $data = $this->service->store($request); 
        return redirect()->route('admin.doctor.index')->withSuccess('The doctor has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.doctor.index')->withSuccess('The doctor has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->getSingleData($request);
        $response['branches'] = $this->service->branches();
        return view('admin.user.edit',$response);
    }
    public function update(UserUpdateRequest $request){
        $data = $this->service->update($request); 
        return redirect()->route('admin.doctor.index')->withSuccess('The doctor’s information has been successfully updated.');
    }
    
    public function profileEdit(Request $request){
        $response['data'] = $this->service->getAdminData($request);
        return view('admin.user.profile_edit',$response);
    }
    public function profileUpdate(AdminProfileUpdateRequest $request){
        $data = $this->service->adminUpdate($request); 
        return redirect()->route('admin.dashboard')->withSuccess('Your profile has been successfully updated.');
    }

}