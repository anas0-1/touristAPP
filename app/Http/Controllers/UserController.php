<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    // Only 'admin' and 'super_admin' can view users
    public function index(Request $request)
    {
        // Check if the authenticated user has 'admin' or 'super_admin' role
        if ($request->user()->hasRole(['admin', 'super_admin'])) {
            return response()->json(User::all(), 200);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    
    public function store(Request $request)
    {
        // Check if the authenticated user has 'admin' or 'super_admin' role
        if ($request->user()->hasRole(['admin', 'super_admin'])) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|string|in:user,admin'
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
    
            // Assign the role to the user
            $user->assignRole($request->role);
    
            return response()->json($user, 201);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    public function update(Request $request, User $user)
    {
        // Check if the authenticated user has 'admin' or 'super_admin' role
        if ($request->user()->hasRole(['admin', 'super_admin'])) {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'sometimes|string|min:8|confirmed',
                'role' => 'sometimes|string|in:user,admin,super_admin'
            ]);
    
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
    
            if ($request->has('name')) {
                $user->name = $request->name;
            }
    
            if ($request->has('email')) {
                $user->email = $request->email;
            }
    
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
    
            if ($request->has('role')) {
                $currentUser = $request->user();
    
                // Check if the role assignment is allowed
                if ($currentUser->hasRole('super_admin')) {
                    $user->syncRoles([$request->role]);
                } elseif ($currentUser->hasRole('admin') && in_array($request->role, ['user', 'admin'])) {
                    // Admin can only assign 'user' or 'admin'
                    $user->syncRoles([$request->role]);
                } else {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            }
    
            $user->save();
    
            return response()->json($user, 200);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    public function destroy(Request $request, User $user)
    {
        // Check if the authenticated user has 'super_admin' role
        if ($request->user()->hasRole('super_admin')) {
            $user->delete();
            return response()->json(['message' => 'User deleted successfully.'], 200);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
}

