<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mood;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\StatsController;

class MoodController extends Controller
{
    // Save mood
    public function store(Request $request)
    {
        $request->validate([
            'mood' => 'required|string'
        ]);

        $moodLevel = $this->mapMoodNameToLevel($request->mood);

        $mood = Mood::create([
            'user_id' => Auth::id(),
            'mood_level' => $moodLevel,
            'notes' => $request->notes ?? '',
            'date' => now()->toDateString()
        ]);

        // Growth logic
        app(\App\Http\Controllers\StatsController::class)->updateStats();

        return response()->json([
            'message' => 'Mood saved',
            'data' => $mood
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

    public function getMoodLog()
    {
        $userId = Auth::id();

        return Mood::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($mood) {
                return [
                    'date' => $mood->created_at->toDateString(),

                    'mood' => match(true) {
                        $mood->mood_level >= 9 => 'joyful',
                        $mood->mood_level >= 7 => 'happy',
                        $mood->mood_level >= 6 => 'content',
                        $mood->mood_level >= 5 => 'neutral',
                        $mood->mood_level >= 3 => 'tired',
                        default => 'sad'
                    },
                    
                    'note' => $mood->notes ?? '',
                    'created_at' => $mood->created_at
                ];
            });
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