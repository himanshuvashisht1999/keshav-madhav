<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::where('guard_name', 'admin')->where('name', '!=', 'Owner')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::where('guard_name', 'admin')->get();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array'
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->withSuccess('Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::where('guard_name', 'admin')->get();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required|array'
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.index')->withSuccess('Role updated successfully.');
    }

    public function delete(Role $role)
    {
        if (in_array($role->name, ['Admin', 'Owner'])) {
            return redirect()->route('admin.roles.index')->withError($role->name . ' role cannot be deleted.');
        }
        log_deletion('User Role', $role->id, [
            'role'        => $role->toArray(),
            'permissions' => $role->permissions ? $role->permissions->pluck('name')->toArray() : []
        ]);
        $role->delete();
        return redirect()->route('admin.roles.index')->withSuccess('Role deleted successfully.');
    }
}
