<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function assignRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $role = Role::findByName($request->role);

        $user->assignRole($role);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function revokeRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $role = Role::findByName($request->role);

        $user->removeRole($role);

        return response()->json(['message' => 'Role revoked successfully']);
    }
}

