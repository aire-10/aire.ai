<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mood;
use Illuminate\Support\Facades\Auth;

class MoodController extends Controller
{
    // Save mood
    public function store(Request $request)
    {
        $request->validate([
            'mood' => 'required|string'
        ]);

        Mood::create([
            'user_id' => Auth::id(),
            'mood' => $request->mood
        ]);

        return response()->json([
            'message' => 'Mood saved'
        ]);
    }

    // Get mood history
    public function history()
    {
        $moods = Mood::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('moodtracker', compact('moods'));
    }
}