<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProgramController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // Show all programs
        $programs = Program::with('activities')->get();
        return response()->json($programs);
    }
    public function store(Request $request)
    {
        $user = Auth::user();
        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string',
            'images' => 'array',
            'images.*' => 'file|mimes:jpeg,png,jpg|max:2048', // Handle image validation
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
                'user_id' => auth()->id(),
            ]);

            // Handle image uploads (if any)
            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('program_images', 'public'); // Store in 'public' disk
                    $images[] = $path;
                }
                $program->images = json_encode($images); // Assuming there's an images column in the programs table
                $program->save();
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
                'program' => $program,
            ], 201);

        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create program and activities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Program $program)
    {
        return response()->json($program->load('activities'));
    }

    public function update(Request $request, Program $program)
    {
        $this->authorize('update', $program);

        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,png,jpeg',
        ]);

        $program->update($request->only('name', 'description', 'duration', 'images'));

        return response()->json($program);
    }

    public function destroy(Program $program)
    {
        $this->authorize('delete', $program);

        $program->delete();

        return response()->json(null, 204);
    }

    public function applications(Program $program)
    {
        $applications = $program->applications()->with('user')->get();
        return response()->json($applications);
    }
}
