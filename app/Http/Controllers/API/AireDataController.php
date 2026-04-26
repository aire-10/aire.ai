<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mood;
use App\Models\Journal;
use App\Models\Grounding;
use App\Models\MoodBooster;
use App\Models\MiniTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AireDataController extends Controller
{
    public function getMoodLog()
    {
        $userId = 1;

        $moods = Mood::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        $log = [];

        foreach ($moods as $mood) {
            $log[] = [
                'date' => $mood->created_at->toDateString(),
                'mood' => $this->mapMoodLevelToName($mood->mood_level),
                'note' => $mood->notes ?? '',
                'ts' => $mood->created_at->timestamp
            ];
        }

        return response()->json($log);
    }

    public function getStreak()
    {
        $userId = 1;
        $streak = 0;
        $currentDate = now()->toDateString();

        $moodDates = Mood::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();

        $checkDate = $currentDate;

        while (in_array($checkDate, $moodDates)) {
            $streak++;
            $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
        }

        return response()->json($streak);
    }

    public function getDaysTracked()
    {
        $userId = 1;

        $count = Mood::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->count();

        return response()->json($count);
    }

    public function getTodayCheckInCount()
    {
        $userId = 1;

        $count = Mood::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->count();

        return response()->json($count);
    }

    public function getLatestMood()
    {
        $userId = 1;

        $latest = Mood::where('user_id', $userId)
            ->latest()
            ->first();

        if ($latest) {
            return response()->json($this->mapMoodLevelToName($latest->mood_level));
        }

        return response()->json(null);
    }

    public function logMood(Request $request)
    {
        $request->validate([
            'mood' => 'required|string',
            'note' => 'nullable|string'
        ]);

        $userId = 1;

        $mood = Mood::create([
            'user_id' => $userId,
            'mood_level' => $this->mapMoodNameToLevel($request->mood),
            'notes' => $request->note,
            'date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'mood' => $mood,
            'streak' => $this->calculateStreak($userId),
            'stageKey' => $this->calculateStageKey($userId)
        ]);
    }

    private function calculateStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();

        $moodDates = Mood::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();

        $checkDate = $currentDate;

        while (in_array($checkDate, $moodDates)) {
            $streak++;
            $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
        }

        return $streak;
    }

    private function calculateStageKey($userId)
    {
        $points = $this->getGrowthPoints($userId);

        if ($points < 10) return 'egg';
        if ($points < 30) return 'caterpillar';
        if ($points < 50) return 'pupa';
        return 'butterfly';
    }

    private function getGrowthPoints($userId)
    {
        $points = 0;

        $points += Mood::where('user_id', $userId)->count();

        $points += Mood::where('user_id', $userId)
            ->whereIn('mood_level', [6,7,8,9,10])
            ->count();

        $points += Journal::where('user_id', $userId)->count();

        $points += Grounding::where('user_id', $userId)->count() * 0.5;

        $points += MoodBooster::where('user_id', $userId)->count() * 0.5;

        $points += MiniTask::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count() * 0.5;

        return round($points, 1);
    }

    private function mapMoodNameToLevel($name)
    {
        return [
            'joyful' => 10,
            'happy' => 8,
            'content' => 6,
            'neutral' => 5,
            'tired' => 3,
            'anxious' => 3,
            'sad' => 2
        ][$name] ?? 5;
    }

    private function mapMoodLevelToName($level)
    {
        return [
            10 => 'joyful',
            9 => 'joyful',
            8 => 'happy',
            7 => 'happy',
            6 => 'content',
            5 => 'neutral',
            4 => 'neutral',
            3 => 'tired',
            2 => 'sad',
            1 => 'sad'
        ][$level] ?? 'neutral';
    }
}