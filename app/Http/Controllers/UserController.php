<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\RegisterRequest;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:admin')->only(['index', 'store', 'update', 'destroy']);
    }
    
    public function index()
    {
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin')->orWhere('name', 'super-admin');
        })->with('roles')->paginate(10);
    
        $customizedUsers = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->isNotEmpty() ? $user->roles->first()->name : null, // Get the first role
            ];
        });
    
        return response()->json([
            'data' => $customizedUsers,
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ]);
    }
    


    public function store(RegisterRequest $request)
    {
        // Check if the role is 'admin'
        if ($request['role'] === 'admin') {
            return response()->json(['message' => 'You are not allowed to create a admin user.'], 403);
        }
    
        DB::beginTransaction(); // Start the transaction
    
        try {
            // Create the user
            $user = User::create([
                'name' => $request['name'],
                'email' => $request['email'],
                'password' => Hash::make($request['password']),
            ]);
    
            // Assign the role
            $user->assignRole($request['role']);
    
            DB::commit(); // Commit the transaction if everything goes well
    
            return response()->json(['message' => 'User created successfully.', 'user' => $user], 201);
    
        } catch (\Exception $e) {
            DB::rollBack(); // Roll back the transaction if any error occurs
    
            return response()->json(['message' => 'User creation failed. Error: ' . $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    
        // Validate only the role
        $validatedData = $request->validate([
            'role' => 'required'
        ]);
    
        // Sync the role with the user
        $user->syncRoles($validatedData['role']);
    
        return response()->json(['message' => 'User role updated successfully.', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }
}
