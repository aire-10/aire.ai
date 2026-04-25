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

        $moodLevel = $this->mapMoodNameToLevel($request->mood);

        Mood::create([
            'user_id' => Auth::id(),
            'mood_level' => $moodLevel,
            'notes' => $request->notes ?? '',
            'date' => now()->toDateString()
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

    // Map mood name to numeric level
    private function mapMoodNameToLevel($name)
    {
        $map = [
            'joyful' => 10,
            'happy' => 8,
            'content' => 6,
            'neutral' => 5,
            'anxious' => 3,
            'tired' => 3,
            'sad' => 2
        ];
        return $map[$name] ?? 5;
    }
}