<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Log in a user and issue an access token.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Attempt to log in the user
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            
            // Create a token
            $token = $user->createToken('Access Token')->accessToken;
            
            // Retrieve the user's roles and permissions
            $roles = $user->getRoleNames(); // Get all roles
            $permissions = $user->getAllPermissions()->pluck('name'); // Get all permissions
    
            // Return the token, user, roles, and permissions
            return response()->json([
                'token' => $token, 
                'user' => $user, 
                'roles' => $roles, 
                'permissions' => $permissions
            ], 200);
        }
    
        return response()->json(['error' => 'Unauthorized'], 401);
    }    
}
