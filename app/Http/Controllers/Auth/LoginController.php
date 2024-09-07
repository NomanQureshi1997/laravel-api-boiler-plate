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
    
    // Retrieve the user's first role and all permission names
    $role = $user->getRoleNames()->first(); // Get the first role
    $permissions = $user->getAllPermissions()->pluck('name'); // Get all permission names
    
    // Remove the roles and permissions from the user object
    $user->setHidden(['roles', 'permissions','password', 'remember_token', 'email_verified_at']);
    
    // Return the token, user, role, and permissions
    return response()->json([
        'token' => $token, 
        'user' => $user, 
        'role' => $role,  // Return only the first role
        'permissions' => $permissions // Return the permission names
    ], 200);
}

return response()->json(['error' => 'Unauthorized'], 401);

    } 
}
