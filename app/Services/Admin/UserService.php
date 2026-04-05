<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\DataTable\Admin\UserDataTable as DataTable;
use App\Models\User;
use App\Models\Branch;

class UserService
{
    protected $datatable;
    protected $user;

    public function __construct(
        DataTable $datatable,
        User $user
    ) {
        $this->datatable = $datatable;
        $this->user = $user;

    }
    public function index(Request $request)
    {
        return true;
    }
    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function store(Request $request)
    {
        // dd($request->all()); 
        $imgName = '';
        if ($request->file('image')) {
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $imgName = "image-" . rand() . "_" . time() . "." . $ext;
            $destinationPath = public_path() . '/assets/user-image';
            $image->move($destinationPath, $imgName);
        }
        $save_data = new User;
        $save_data->first_name = $request->first_name;
        $save_data->last_name = $request->last_name;
        $save_data->email = $request->email;
        $save_data->phone = $request->phone;
        $save_data->password = Hash::make($request->input('password'));
        $save_data->image = $imgName;
        $save_data->gender = $request->gender;
        $save_data->address = $request->address;
        $save_data->role_id = $request->role_id;
        $save_data->status = $request->status;
        $save_data->facebook = $request->facebook;
        $save_data->linkdin = $request->linkdin;
        $save_data->instagram = $request->instagram;
        $save_data->twitter = $request->twitter;

        // Sync role_id for DB integrity
        if ($request->filled('role_name')) {
            $role = \Spatie\Permission\Models\Role::where('name', $request->role_name)->where('guard_name', 'admin')->first();
            if ($role) {
                $save_data->role_id = $role->id;
            }
        }

        $save_data->save();

        // Sync Spatie Roles
        if ($request->has('role_name')) {
            $save_data->syncRoles($request->role_name);
        }

        return true;
    }

    public function getUser()
    {
        $data = User::where('status', 1)->where('parent_id', 0)->get();
        return $data;
    }
    public function delete(Request $request)
    {
        $data = User::where('id', $request->id)->delete();
        return $data;
    }
    public function getSingleData(Request $request)
    {
        $data = User::where('id', $request->id)->first();
        return $data;
    }
    public function update(Request $request)
    {
        $update_data = User::find($request->id);
        if ($request->file('image')) {
            $oldImageName = $update_data->getRawOriginal('image');
            if ($oldImageName) {
                $oldImagePath = public_path('assets/user-image/' . $oldImageName);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $imgName = "image-" . rand() . "_" . time() . "." . $ext;
            $destinationPath = public_path() . '/assets/user-image';
            $image->move($destinationPath, $imgName);
            $update_data->image = $imgName;
        }
        $update_data->first_name = $request->first_name;
        $update_data->last_name = $request->last_name;
        $update_data->email = $request->email;
        $update_data->phone = $request->phone;
        $update_data->gender = $request->gender;
        $update_data->address = $request->address;
        $update_data->role_id = $request->role_id;
        $update_data->status = $request->status;
        $update_data->facebook = $request->facebook;
        $update_data->linkdin = $request->linkdin;
        $update_data->instagram = $request->instagram;
        $update_data->twitter = $request->twitter;

        if ($request->filled('password')) {
            $update_data->password = Hash::make($request->input('password'));
        }

        // Sync role_id for DB integrity
        if ($request->filled('role_name')) {
            $role = \Spatie\Permission\Models\Role::where('name', $request->role_name)->where('guard_name', 'admin')->first();
            if ($role) {
                $update_data->role_id = $role->id;
            }
        }

        $update_data->save();

        // Sync Spatie Roles
        if ($request->has('role_name')) {
            $update_data->syncRoles($request->role_name);
        }

        return true;
    }
    public function getAdminData(Request $request)
    {
        $data = User::first();
        return $data;
    }
    public function branches()
    {
        $data = \App\Models\MasterWarehouse::where('status', 1)->get();
        return $data;
    }
    public function adminUpdate(Request $request)
    {
        $update_data = User::find($request->id);
        if ($request->file('image')) {
            $oldImageName = $update_data->getRawOriginal('image');
            if ($oldImageName) {
                $oldImagePath = public_path('assets/user-image/' . $oldImageName);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $imgName = "image-" . rand() . "_" . time() . "." . $ext;
            $destinationPath = public_path() . '/assets/user-image';
            $image->move($destinationPath, $imgName);
            $update_data->image = $imgName;
        }
        $update_data->first_name = $request->first_name;
        $update_data->last_name = $request->last_name;
        $update_data->email = $request->email;
        $update_data->phone = $request->phone;
        if ($request->filled('password')) {
            $update_data->password = Hash::make($request->input('password'));
        }
        $update_data->save();

        return true;
    }


}