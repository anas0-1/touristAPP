<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Activity;
use App\Models\Media;
use App\Http\Controllers\ActivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProgramController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // Show all programs
        $programs = Program::with('activities','media')->get();
        return response()->json($programs);
    }
    private function formatProgramData(Program $program)
    {
        $formattedProgram = $program->toArray();
        $formattedProgram['averageRating'] = $program->averageRating();
        $formattedProgram['firstImage'] = $program->media->first() ? $program->media->first()->url : null;
        return $formattedProgram;
    }
    public function store(Request $request)
    {
        $user = Auth::user();
    
        // Check if the user can create a new program (limited to 2)
        if (!$this->authorize('create', Program::class)) {
            return response()->json([
                'message' => 'You have reached the free use limit. Please subscribe to create more programs.',
            ], 403); 
        }
    
        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'starting_date' => 'required|date',
            'images' => 'array',
            'images.*' => 'file|mimes:jpeg,png,jpg|max:2048',
            'activities' => 'required|array',
            'activities.*.name' => 'required|string|max:255',
            'activities.*.time' => 'required|string|max:255',
            'activities.*.description' => 'required|string|max:255',
            'activities.*.duration' => 'required|string',
            'activities.*.location' => 'required|string|max:255',
        ]);
    
        // Wrap the operations in a database transaction
        DB::beginTransaction();
        try {
            // Save the program first
            $program = Program::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'duration' => $validatedData['duration'],
                'location' => $validatedData['location'],
                'price' => $validatedData['price'],
                'starting_date' => $validatedData['starting_date'],
                'user_id' => $user->id,
            ]);
    
            // Handle image uploads (if any)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('program_images', 'public'); // Store in 'public' disk
                    Media::create([
                        'program_id' => $program->id,
                        'url' => $path,
                    ]);
                }
            }
    
            // Save each activity related to the program
            foreach ($validatedData['activities'] as $activityData) {
                Activity::create([
                    'program_id' => $program->id,
                    'name' => $activityData['name'],
                    'description' => $activityData['description'],
                    'time' => $activityData['time'],
                    'duration' => $activityData['duration'],
                    'location' => $activityData['location'],
                ]);
            }
    
            // Commit the transaction
            DB::commit();
    
            return response()->json([
                'message' => 'Program and activities created successfully!',
                'program' => $program->load('activities', 'media'), // Load activities and media
                'media' => $program->media->map(function ($media) {
                    return asset('storage/' . $media->url);
                }),
            ], 201);
    
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create program and activities',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }    

    public function show(Program $program)
    {
        $program->load('activities', 'media');
        $program->media = $program->media->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => asset('storage/' . $media->url)
            ];
        });
    
        return response()->json($program);
    }
    

public function update(Request $request, $id)
{
    $user = Auth::user();
    
    // Find the program by ID
    $program = Program::findOrFail($id);

    // Authorize the action
    $this->authorize('update', $program);

    // Validate request data
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'duration' => 'required|string',
        'location' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'starting_date' => 'required|date',
        'images' => 'array',
        'images.*' => 'file|mimes:jpeg,png,jpg|max:2048',
        'activities' => 'required|array',
        'activities.*.id' => 'nullable|integer|exists:activities,id',
        'activities.*.name' => 'required|string|max:255',
        'activities.*.time' => 'required|string|max:255',
        'activities.*.description' => 'required|string|max:255',
        'activities.*.duration' => 'required|string',
        'activities.*.location' => 'required|string|max:255',
    ]);

    // Wrap the operations in a database transaction
    DB::beginTransaction();
    try {
        // Update program fields
        $program->update($validatedData);

        // Handle activities update/addition/deletion
        $existingActivityIds = $program->activities->pluck('id')->toArray();
        $updatedActivityIds = [];

        foreach ($validatedData['activities'] as $activityData) {
            if (isset($activityData['id'])) {
                // Update existing activity
                $activity = Activity::findOrFail($activityData['id']);
                $activity->update($activityData);
                $updatedActivityIds[] = $activity->id;
            } else {
                // Create new activity
                $newActivity = $program->activities()->create($activityData);
                $updatedActivityIds[] = $newActivity->id;
            }
        }

        // Delete any activities that were not included in the update
        $activitiesToDelete = array_diff($existingActivityIds, $updatedActivityIds);
        Activity::destroy($activitiesToDelete);

        // Handle image upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('program_images', 'public');
                $program->media()->create(['url' => $path]);
            }
        }

        // Commit the transaction
        DB::commit();

        // Load the updated program with its relationships
        $program->load('activities', 'media');

        return response()->json([
            'message' => 'Program, activities, and images updated successfully!',
            'program' => $program,
            'media' => $program->media->map(function ($media) {
                return [
                    'id' => $media->id,
                    'url' => asset('storage/' . $media->url)
                ];
            }),
        ], 200);

    } catch (\Exception $e) {
        // Rollback the transaction in case of an error
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to update program and activities',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function destroy($id)
{
    $program = Program::findOrFail($id);

    // Authorize the delete action
    if (!$this->authorize('delete', $program)) {
        return response()->json([
            'message' => 'You are not authorized to delete this program.'
        ], 403);
    }

    // Wrap the operations in a database transaction
    DB::beginTransaction();
    try {
        // Delete related activities
        foreach ($program->activities as $activity) {
            $activity->delete();
        }

        // Delete related media (images)
        foreach ($program->media as $media) {
            Storage::delete('public/' . $media->url); // Delete image from storage
            $media->delete(); // Remove the media record from the database
        }

        // Delete the program itself
        $program->delete();

        // Commit the transaction
        DB::commit();

        return response()->json([
            'message' => 'Program, activities, and images deleted successfully!'
        ], 200);

    } catch (\Exception $e) {
        // Rollback the transaction in case of an error
        DB::rollBack();
        return response()->json([
            'message' => 'Failed to delete program',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
}
    public function applications(Program $program)
    {
        $applications = $program->applications()->with('user')->get();
        return response()->json($applications);
    }
}