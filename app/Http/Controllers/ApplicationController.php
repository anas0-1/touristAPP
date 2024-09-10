<?php

// app/Http/Controllers/ApplicationController.php
namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApplicationController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request, Program $program)
    {
        $user = Auth::user();

        // Check if the user has already applied
        if ($program->applications()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'You have already applied for this program.'], 403);
        }

        // Check application limit
        $maxApplications = $user->hasRole('admin') || $user->hasRole('super_admin') ? PHP_INT_MAX : 1;
        if (Application::where('user_id', $user->id)->count() >= $maxApplications) {
            return response()->json(['error' => 'Application limit reached.'], 403);
        }

        $application = $program->applications()->create(['user_id' => $user->id]);

        return response()->json($application, 201);
    }

    public function destroy(Application $application)
    {
        $this->authorize('delete', $application);

        $application->delete();

        return response()->json(null, 204);
    }
}
