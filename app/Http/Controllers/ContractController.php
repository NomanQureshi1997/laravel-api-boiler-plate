<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractController extends Controller
{
    
    public function index(Request $request)
    {
        $user = $request->user(); // Get the authenticated user
    
        if ($user->hasRole('admin')) {
            // If the user is a admin, return all contracts
            $contracts = Contract::with('users')->paginate(10);
        } else {
            // Otherwise, return only contracts that the user is associated with
            $contracts = $user->contracts()->with('users')->paginate(10);
        }
    
        $formattedContracts = $contracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'title' => $contract->title,
                'description' => $contract->description,
                'start_date' => $contract->start_date,
                'end_date' => $contract->end_date,
                // Include user information if needed
                'users' => $contract->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                    ];
                }),
            ];
        });
    
        return response()->json([
            'data' => $formattedContracts,
            'current_page' => $contracts->currentPage(),
            'per_page' => $contracts->perPage(),
            'total' => $contracts->total(),
        ]);
    }
    
    public function show(Request $request, $id)
    {
        $user = $request->user(); // Get the authenticated user
        $contract = Contract::with('users')->find($id);
    
        if (!$contract) {
            return response()->json(['message' => 'Contract not found.'], 404);
        }
    
        // Check if the user is a admin or associated with the contract
        if (!$user->hasRole('admin') && !$contract->users->contains($user->id)) {
            return response()->json(['message' => 'Unauthorized to access this contract.'], 403);
        }
    
        $formattedContract = [
            'id' => $contract->id,
            'title' => $contract->title,
            'description' => $contract->description,
            'start_date' => $contract->start_date,
            'end_date' => $contract->end_date,
            'users' => $contract->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            }),
        ];
    
        return response()->json($formattedContract);
    }

    public function store(Request $request)
    {
        $user = $request->user(); // Get the authenticated user
    
        // Check if the user is a admin
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized to create contracts.'], 403);
        }
    
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'user_ids' => 'required|array', // Array of user IDs to associate with the contract
            'user_ids.*' => 'exists:users,id',
        ]);
    
        // Create the contract
        $contract = Contract::create($validatedData);
    
        // Attach users to the contract
        $contract->users()->attach($validatedData['user_ids']);
    
        return response()->json(['message' => 'Contract created successfully.', 'contract' => $contract], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user(); // Get the authenticated user
    
        // Check if the user is a admin
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized to update contracts.'], 403);
        }
    
        $contract = Contract::find($id);
    
        if (!$contract) {
            return response()->json(['message' => 'Contract not found.'], 404);
        }
    
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'user_ids' => 'required|array', // Array of user IDs to associate with the contract
            'user_ids.*' => 'exists:users,id',
        ]);
    
        // Update contract details
        $contract->update($validatedData);
    
        // Sync users with the contract
        $contract->users()->sync($validatedData['user_ids']);
    
        return response()->json(['message' => 'Contract updated successfully.', 'contract' => $contract]);
    }
    

    public function destroy($id)
    {
        $user = auth()->user(); // Get the authenticated user
    
        // Check if the user is a admin
        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized to delete contracts.'], 403);
        }
    
        $contract = Contract::find($id);
    
        if (!$contract) {
            return response()->json(['message' => 'Contract not found.'], 404);
        }
    
        // Detach users before deleting
        $contract->users()->detach();
        $contract->delete();
    
        return response()->json(['message' => 'Contract deleted successfully.']);
    }
}
