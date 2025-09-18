<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;

class UserDataTable  {

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function indexList($request)
    {
        $queue = User::query();
    
        return DataTables::of($queue)->addIndexColumn()
    
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                $query->orWhere('first_name', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('first_name') && !empty($request->first_name)) {
                    $query->where('first_name', 'like', "%{$request->get('first_name')}%");
                }
                if ($request->has('last_name') && !empty($request->last_name)) {
                    $query->where('last_name', 'like', "%{$request->get('last_name')}%");
                }
                if ($request->has('title') && !empty($request->title)) {
                    $query->where('title', 'like', "%{$request->get('title')}%");
                }
                if ($request->has('phone') && !empty($request->phone)) {
                    $query->where('phone', 'like', "%{$request->get('phone')}%");
                }
                if ($request->has('role_id') && $request->filled('role_id')) {
                    $query->where('role_id', $request->get('role_id'));
                }
                if ($request->has('status') && $request->filled('status')) {
                    $query->where('status', $request->get('status'));
                }
                $query->where('role_id', '<>', 1);
            })

            // ->editColumn('name', function ($user) {
            //     return $user->first_name . ' ' . $user->last_name;
            // })
            ->editColumn('name', function ($user) {
                return $user->first_name;
            })
            ->editColumn('role_id', function ($queue) {
                $role_id = $queue->role_id;
                return ($role_id == 2) ? 'Staff' : 'User';
            })
            
            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            }) 
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                return '
                    <a href="' . route('admin.doctor.edit', ['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                    <a href="javascript:void(0)" class="ml-1" onClick="deleteUser(' . $parameter . ')" data-id="' . $parameter . '" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="fas fa-trash text-danger"></i></a>
                    ';
            })
            ->rawColumns(['action', 'status','role_id'])
            ->make(true);
    }
    
}