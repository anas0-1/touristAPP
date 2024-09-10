<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|string',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpg,png,jpeg',
        ]);

        if ($user->programs()->count() >= 2 && !$user->hasRole('admin') && !$user->hasRole('super_admin')) {
            return response()->json(['error' => 'You can only create up to 2 programs.'], 403);
        }

        $program = $user->programs()->create($request->only('name', 'description', 'duration', 'images'));

        return response()->json($program, 201);
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
