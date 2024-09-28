<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApplicationsController extends Controller
{
    // Store a new application
    public function store(Request $request, $programId)
    {
        \Log::info('Received application submission for program ID: ' . $programId);

        $user = Auth::user();
        
        // Check if the program exists
        $program = Program::find($programId);
        if (!$program) {
            \Log::error('Program not found for ID: ' . $programId);
            return response()->json(['message' => 'Program not found.'], 404);
        }

        // Validate the request data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tickets' => 'required|integer|min:1',
        ]);

        // Ensure the user has not already applied for this program
        if (Application::where('user_id', $user->id)->where('program_id', $programId)->exists()) {
            return response()->json(['message' => 'You have already applied for this program'], 400);
        }

        // Create the application
        $application = Application::create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'tickets' => $validated['tickets'],
        ]);

        \Log::info('Application submitted successfully for program ID: ' . $programId);

        return response()->json(['message' => 'Application submitted successfully', 'application' => $application], 201);
    }

    // Show all applicants for a program (only accessible to program owner)
    public function index($programId)
    {
        $user = Auth::user();
        $program = Program::findOrFail($programId);

        // Only the program owner can see applicants
        if ($program->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $applications = $program->applications()->with('user')->get();

        return response()->json($applications);
    }
    
    // Update an application (only if the program has not started yet)
    public function update(Request $request, $programId, $applicationId)
    {
        $user = Auth::user();
        $program = Program::findOrFail($programId);
        $application = Application::findOrFail($applicationId);

        // Ensure the user owns this application
        if ($application->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if the program has already started
        if (now()->greaterThanOrEqualTo($program->starting_date)) {
            return response()->json(['message' => 'You cannot update your application after the program has started'], 400);
        }

        // Validate the request data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tickets' => 'required|integer|min:1',
        ]);

        // Update the application
        $application->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'tickets' => $validated['tickets'],
        ]);

        return response()->json(['message' => 'Application updated successfully', 'application' => $application], 200);
    }

    // Delete an applicant (only accessible to program owner)
    public function destroy($programId, $applicationId)
    {
        $user = Auth::user();
        $program = Program::findOrFail($programId);
        $application = Application::findOrFail($applicationId);

        // Only the program owner can delete applications
        if ($program->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $application->delete();

        return response()->json(['message' => 'Application deleted successfully']);
    }
}