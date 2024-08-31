<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Requests\RegisterRequest; // Import the form request

class RegisterController extends Controller
{
    /**
     * Register a new user and issue an access token.
     *
     * @param \App\Http\Requests\RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        // Use a transaction to handle rollback in case of failure
        DB::beginTransaction();

        try {
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create a personal access token
            $token = $user->createToken('Personal Access Token')->accessToken; // Use plainTextToken to get the token string

            DB::commit(); // Commit the transaction

            return response()->json(['token' => $token], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if there is an error

            // Return a more user-friendly error message
            return response()->json(['error' => 'Registration failed', 'message' => $e->getMessage()], 500);
        }
    }
}
