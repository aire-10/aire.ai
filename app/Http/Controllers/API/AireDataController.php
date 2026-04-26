<?php

namespace App\Http\Controllers\Api;

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
    public function __construct()
    {
        Auth::id()
    }
    
    public function getMoodLog()
    {
        $moods = Mood::where('user_id', 1)
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
        $streak = 0;
        $currentDate = now()->toDateString();
        
        $moodDates = Mood::where('user_id', Auth::id())
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
        $count = Mood::where('user_id', Auth::id())
            ->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->count();
        
        return response()->json($count);
    }
    
    public function getTodayCheckInCount()
    {
        $count = Mood::where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->count();
        
        return response()->json($count);
    }
    
    public function getLatestMood()
    {
        $latest = Mood::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($latest) {
            return response()->json($this->mapMoodLevelToName($latest->mood_level));
        }
        
        return response()->json(null);
    }
    
    public function getMoodMeta()
    {
        return response()->json([
            'joyful' => ['emoji' => '😊', 'label' => 'Joyful', 'color' => '#3a8c3a'],
            'happy' => ['emoji' => '🙂', 'label' => 'Happy', 'color' => '#5aab5a'],
            'content' => ['emoji' => '😌', 'label' => 'Content', 'color' => '#7aab72'],
            'neutral' => ['emoji' => '😐', 'label' => 'Neutral', 'color' => '#b0a060'],
            'tired' => ['emoji' => '😴', 'label' => 'Tired', 'color' => '#c47a5a'],
            'anxious' => ['emoji' => '😰', 'label' => 'Anxious', 'color' => '#c47a5a'],
            'sad' => ['emoji' => '😢', 'label' => 'Sad', 'color' => '#a07070']
        ]);
    }
    
    public function getPositiveMoods()
    {
        return response()->json(['joyful', 'happy', 'content', 'calm', 'grateful']);
    }
    
    /**
     * Log a new mood entry
     */
    public function logMood(Request $request)
    {
        $request->validate([
            'mood' => 'required|string',
            'note' => 'nullable|string'
        ]);
        
        $mood = Mood::create([
            'user_id' => 1,
            'mood_level' => $this->mapMoodNameToLevel($request->mood),
            'notes' => $request->note,
            'date' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'mood' => $mood,
            'streak' => $this->calculateStreak(1)
            'stageKey' => $this->calculateStageKey(1)
        ]);
    }
    
    /**
     * Calculate current streak for a user
     */
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
    
    /**
     * Calculate growth stage key based on points
     */
    private function calculateStageKey($userId)
    {
        $points = $this->getGrowthPoints($userId);
        
        if ($points < 10) return 'egg';
        if ($points < 30) return 'caterpillar';
        if ($points < 50) return 'pupa';
        return 'butterfly';
    }
    
    /**
     * Calculate total growth points for a user
     */
    private function getGrowthPoints($userId)
    {
        $points = 0;
        
        // Mood entries: each mood = 1 point
        $moodCount = Mood::where('user_id', $userId)->count();
        $points += $moodCount;
        
        // Positive moods: +1 extra point each
        $positiveMoods = Mood::where('user_id', $userId)
            ->whereIn('mood_level', [6, 7, 8, 9, 10])
            ->count();
        $points += $positiveMoods;
        
        // Journal entries: +1 point each
        $journalCount = Journal::where('user_id', $userId)->count();
        $points += $journalCount;
        
        // Grounding exercises: +0.5 points each
        $groundingCount = Grounding::where('user_id', $userId)->count();
        $points += $groundingCount * 0.5;
        
        // Mood boosters: +0.5 points each
        $boosterCount = MoodBooster::where('user_id', $userId)->count();
        $points += $boosterCount * 0.5;
        
        // Completed mini tasks: +0.5 points each
        $taskCount = MiniTask::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count();
        $points += $taskCount * 0.5;
        
        return round($points, 1);
    }
    
    /**
     * Map mood name to numeric level
     */
    private function mapMoodNameToLevel($name)
    {
        $mapping = [
            'joyful' => 10,
            'happy' => 8,
            'content' => 6,
            'neutral' => 5,
            'tired' => 3,
            'anxious' => 3,
            'sad' => 2
        ];
        
        return $mapping[$name] ?? 5;
    }
    
    /**
     * Map mood level to name
     */
    private function mapMoodLevelToName($level)
    {
        $mapping = [
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
        ];
        
        return $mapping[$level] ?? 'neutral';
    }
}