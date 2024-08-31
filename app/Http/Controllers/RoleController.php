<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        // Prevent creating the 'admin' role manually
        if (strtolower($request->name) === 'admin') {
            return response()->json(['error' => 'Cannot create the admin role.'], 403);
        }

        $role = Role::create(['name' => $request->name]);

        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        // Prevent updating the 'admin' role
        if (strtolower($role->name) === 'admin') {
            return response()->json(['error' => 'Cannot modify the admin role.'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return response()->json(['message' => 'Role updated successfully', 'role' => $role]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deleting the 'admin' role
        if (strtolower($role->name) === 'admin') {
            return response()->json(['error' => 'Cannot delete the admin role.'], 403);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }

    // Assign Permission to Role
    public function assignPermission(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);

        // Prevent assigning permissions to the 'admin' role
        if (strtolower($role->name) === 'admin') {
            return response()->json(['error' => 'Cannot modify permissions of the admin role.'], 403);
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $permissions = Permission::whereIn('name', $request->permissions)->get();

        // Assign the permissions to the role
        $role->syncPermissions($permissions);

        return response()->json(['message' => 'Permissions assigned successfully', 'role' => $role, 'permissions' => $role->getAllPermissions()], 200);
    }

    // Revoke Permission from Role
    public function revokePermission(Request $request, $roleId)
    {
        $role = Role::findOrFail($roleId);

        // Prevent revoking permissions from the 'admin' role
        if (strtolower($role->name) === 'admin') {
            return response()->json(['error' => 'Cannot modify permissions of the admin role.'], 403);
        }

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $permissions = Permission::whereIn('name', $request->permissions)->get();

        // Revoke the permissions from the role
        foreach ($permissions as $permission) {
            $role->revokePermissionTo($permission);
        }

        return response()->json(['message' => 'Permissions revoked successfully', 'role' => $role, 'permissions' => $role->getAllPermissions()], 200);
    }
}
