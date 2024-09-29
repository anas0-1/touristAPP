<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Program;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request, Program $program)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $rating = Rating::updateOrCreate(
            ['user_id' => auth()->id(), 'program_id' => $program->id],
            ['rating' => $request->rating]
        );

        return response()->json([
            'rating' => $rating,
            'average_rating' => $program->averageRating(),
        ]);
    }
}