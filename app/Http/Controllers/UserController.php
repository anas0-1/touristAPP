<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function index(Request $request)
{
    // Check if the authenticated user has 'admin' or 'super_admin' role
    if ($request->user()->hasRole(['admin', 'super_admin','user'])) {
        // Eager load the 'roles' relationship
        $users = User::with('roles')->get();

        // You can map over the users and return a custom response if needed
        $usersWithRoles = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name'), // Return only the role names
                'created_at' => $user->created_at,
            ];
        });

        return response()->json($usersWithRoles, 200);
    }

    return response()->json(['error' => 'Unauthorized'], 403);
}

public function show(User $user)
{
    $user->load('programs'); 
    return response()->json($user);
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
    // Check if the authenticated user can update this user
    $currentUser = $request->user();

    if (!$currentUser->hasRole(['admin', 'super_admin']) && $currentUser->id !== $user->id) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|string|max:255',
        'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        'password' => 'sometimes|string|min:8|confirmed',
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

    $user->save();

    return response()->json($user, 200);
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