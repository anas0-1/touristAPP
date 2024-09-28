<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class ActivityController extends Controller
{
    use AuthorizesRequests;
    public function index(Program $program)
    {
        // Load activities related to the program
        $activities = $program->activities;

        // Return the activities as a JSON response
        return response()->json($activities);
    }
    public function show(Program $program, Activity $activity)
    {
        // Ensure the activity belongs to the program
        if ($activity->program_id !== $program->id) {
            return response()->json(['error' => 'This activity does not belong to this program'], 404);
        }

        // Return the activity details
        return response()->json($activity);
    }

    public function store(Request $request, $programId)
    {
        $program = Program::findOrFail($programId);
        $this->authorize('create', [Activity::class, $program]);

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'time' => 'required|string',
            'duration' => 'required|string',
            'location' => 'required|string',

        ]);

        // Create activity
        $activity = $program->activities()->create($request->only('name', 'description', 'time','duration','location'));

        return response()->json($activity, 201);
    }

    public function update(Request $request, $programId, $activityId)
{
    $program = Program::findOrFail($programId);
    $activity = Activity::findOrFail($activityId);
    
    // Authorize the update action for the current user
    $this->authorize('update', [$activity, $program]);

    // Validation (including duration and location fields)
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'time' => 'required|string',
        'duration' => 'required|string',
        'location' => 'required|string',
    ]);

    // Update the activity
    $activity->update($request->only('name', 'description', 'time', 'duration', 'location'));

    // Return the updated activity
    return response()->json($activity);
}

    public function destroy($programId, $activityId)
    {
        $program = Program::findOrFail($programId);
        $activity = Activity::findOrFail($activityId);
        $this->authorize('delete', [$activity, $program]);

        // Delete activity
        $activity->delete();

        return response()->json(null, 204);
    }
}
