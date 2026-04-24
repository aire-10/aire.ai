<?php

namespace App\Http\Controllers;

use App\Models\MoodBooster;
use App\Models\Mood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodBoosterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the mood booster page
     */
    public function index()
    {
        return view('moodbooster');
    }
    
    /**
     * Update mood from the instant mood check-in
     */
    public function updateMood(Request $request)
    {
        $request->validate([
            'mood' => 'required|string|in:happy,neutral,anxious,sad'
        ]);
        
        $moodLevel = $this->mapMoodNameToLevel($request->mood);
        
        $mood = Mood::create([
            'user_id' => Auth::id(),
            'mood_level' => $moodLevel,
            'notes' => 'Mood booster check-in',
            'date' => now()->toDateString(),
            'created_at' => now()
        ]);
        
        // Get updated stats
        $streak = $this->calculateStreak(Auth::id());
        $stageKey = $this->calculateStageKey(Auth::id());
        
        return response()->json([
            'success' => true,
            'message' => 'Mood updated successfully',
            'mood' => $request->mood,
            'streak' => $streak,
            'stageKey' => $stageKey
        ]);
    }
    
    /**
     * Complete a mood booster activity (Body Booster, Mini Task, Mind Reset, Mood Lifting)
     */
    public function completeActivity(Request $request)
    {
        $request->validate([
            'activity_type' => 'required|string|in:bodybooster,minitask,mindreset,moodlifting',
            'activity_name' => 'nullable|string',
            'duration' => 'nullable|integer'
        ]);
        
        $userId = Auth::id();
        $today = now()->toDateString();
        $activityType = $request->activity_type;
        
        // Check if already completed today
        $alreadyCompleted = MoodBooster::where('user_id', $userId)
            ->where('booster_type', $activityType)
            ->whereDate('completed_at', $today)
            ->exists();
        
        if ($alreadyCompleted) {
            return response()->json([
                'success' => false,
                'message' => 'You already completed this activity today!',
                'already_completed' => true
            ]);
        }
        
        // Record completion
        $booster = MoodBooster::create([
            'user_id' => $userId,
            'booster_type' => $activityType,
            'activity_name' => $request->activity_name,
            'duration' => $request->duration ?? 0,
            'mood_before' => null,
            'mood_after' => null,
            'completed_at' => now()
        ]);
        
        // Award XP via AireData (handled in JS, but we can also track here)
        $points = 0.5;
        
        return response()->json([
            'success' => true,
            'message' => 'Activity completed! +0.5 XP',
            'points' => $points,
            'activity_type' => $activityType,
            'completed_at' => now()->toISOString()
        ]);
    }
    
    /**
     * Get today's completion status for all booster types
     */
    public function getTodayStatus()
    {
        $userId = Auth::id();
        $today = now()->toDateString();
        
        $completedTypes = MoodBooster::where('user_id', $userId)
            ->whereDate('completed_at', $today)
            ->pluck('booster_type')
            ->toArray();
        
        return response()->json([
            'bodybooster' => in_array('bodybooster', $completedTypes),
            'minitask' => in_array('minitask', $completedTypes),
            'mindreset' => in_array('mindreset', $completedTypes),
            'moodlifting' => in_array('moodlifting', $completedTypes),
            'all_completed' => count($completedTypes) >= 4
        ]);
    }
    
    /**
     * Get user's streak (days with any activity completed)
     */
    public function getStreak()
    {
        $streak = $this->calculateStreak(Auth::id());
        
        return response()->json([
            'streak' => $streak,
            'streak_display' => $streak . ' Day Streak 🔥'
        ]);
    }
    
    /**
     * Get weekly progress stats
     */
    public function getWeeklyProgress()
    {
        $userId = Auth::id();
        $last7Days = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $completedCount = MoodBooster::where('user_id', $userId)
                ->whereDate('completed_at', $date)
                ->count();
            
            $last7Days[] = [
                'date' => $date,
                'day' => now()->subDays($i)->format('D'),
                'completed' => $completedCount,
                'has_activity' => $completedCount > 0
            ];
        }
        
        $totalCompleted = MoodBooster::where('user_id', $userId)
            ->whereBetween('completed_at', [now()->subDays(6), now()])
            ->count();
        
        $percentage = round(($totalCompleted / 28) * 100); // 4 activities * 7 days = 28 max
        
        return response()->json([
            'weekly_data' => $last7Days,
            'total_completed' => $totalCompleted,
            'percentage' => min($percentage, 100),
            'streak_days' => $this->calculateActivityStreak($userId)
        ]);
    }
    
    /**
     * Get mood booster history
     */
    public function getHistory(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->get('limit', 30);
        
        $history = MoodBooster::where('user_id', $userId)
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->booster_type,
                    'name' => $item->activity_name,
                    'duration' => $item->duration,
                    'completed_at' => $item->completed_at,
                    'date' => $item->completed_at->toDateString()
                ];
            });
        
        return response()->json($history);
    }
    
    /**
     * Get stats for the sidebar progress card
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        // Calculate current streak
        $streak = $this->calculateActivityStreak($userId);
        
        // Calculate best streak
        $bestStreak = $this->calculateBestStreak($userId);
        
        // Calculate total completions
        $totalCompletions = MoodBooster::where('user_id', $userId)->count();
        
        // Calculate weekly completion percentage
        $weeklyCompletions = MoodBooster::where('user_id', $userId)
            ->whereBetween('completed_at', [now()->subDays(6), now()])
            ->count();
        $weeklyPercentage = round(($weeklyCompletions / 28) * 100);
        
        // Get most active booster type
        $mostActive = MoodBooster::where('user_id', $userId)
            ->select('booster_type')
            ->groupBy('booster_type')
            ->orderByRaw('COUNT(*) DESC')
            ->first();
        
        return response()->json([
            'current_streak' => $streak,
            'best_streak' => $bestStreak,
            'total_completions' => $totalCompletions,
            'weekly_percentage' => min($weeklyPercentage, 100),
            'most_active' => $mostActive ? $mostActive->booster_type : null,
            'streak_display' => $streak . ' Day Streak 🔥'
        ]);
    }
    
    /**
     * Save mood lifting thought/cross-off progress
     */
    public function saveMoodLiftingProgress(Request $request)
    {
        $request->validate([
            'completed_thoughts' => 'required|array',
            'thoughts_count' => 'required|integer'
        ]);
        
        $userId = Auth::id();
        $today = now()->toDateString();
        
        // Store progress in session or create a mood_lifting_progress table
        $progressKey = 'moodlifting_progress_' . $today;
        session()->put($progressKey, [
            'completed_thoughts' => $request->completed_thoughts,
            'count' => $request->thoughts_count,
            'updated_at' => now()
        ]);
        
        // If all thoughts completed, record as achievement
        $totalThoughts = 5; // Default, but could be dynamic
        if ($request->thoughts_count >= $totalThoughts) {
            $alreadyCompleted = MoodBooster::where('user_id', $userId)
                ->where('booster_type', 'moodlifting')
                ->whereDate('completed_at', $today)
                ->exists();
            
            if (!$alreadyCompleted) {
                MoodBooster::create([
                    'user_id' => $userId,
                    'booster_type' => 'moodlifting',
                    'activity_name' => 'Complete Mood Lifting',
                    'duration' => 0,
                    'completed_at' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'just_completed' => true,
                    'message' => 'Mood lifting completed! +0.5 XP'
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'saved' => true
        ]);
    }
    
    /**
     * Get mood lifting progress for today
     */
    public function getMoodLiftingProgress()
    {
        $today = now()->toDateString();
        $progressKey = 'moodlifting_progress_' . $today;
        
        $progress = session()->get($progressKey, [
            'completed_thoughts' => [],
            'count' => 0
        ]);
        
        return response()->json($progress);
    }
    
    // ────────────────── PRIVATE HELPER METHODS ──────────────────
    
    /**
     * Map mood name to numeric level
     */
    private function mapMoodNameToLevel($name)
    {
        $mapping = [
            'happy' => 8,
            'neutral' => 5,
            'anxious' => 3,
            'sad' => 2
        ];
        
        return $mapping[$name] ?? 5;
    }
    
    /**
     * Calculate user streak based on daily activity completion
     */
    private function calculateActivityStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();
        
        // Get all dates where user completed any activity
        $completionDates = MoodBooster::where('user_id', $userId)
            ->selectRaw('DATE(completed_at) as date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();
        
        $checkDate = $currentDate;
        while (in_array($checkDate, $completionDates)) {
            $streak++;
            $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
        }
        
        return $streak;
    }
    
    /**
     * Calculate best streak ever
     */
    private function calculateBestStreak($userId)
    {
        $completionDates = MoodBooster::where('user_id', $userId)
            ->selectRaw('DATE(completed_at) as date')
            ->distinct()
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->toArray();
        
        $longestStreak = 0;
        $currentStreak = 0;
        $previousDate = null;
        
        foreach ($completionDates as $date) {
            if ($previousDate && strtotime($date) === strtotime($previousDate . ' +1 day')) {
                $currentStreak++;
            } else {
                $currentStreak = 1;
            }
            
            $longestStreak = max($longestStreak, $currentStreak);
            $previousDate = $date;
        }
        
        return $longestStreak;
    }
    
    /**
     * Calculate overall streak (from mood tracking)
     */
    private function calculateStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();
        
        $moodDates = Mood::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as date')
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
     * Calculate stage key based on points
     */
    private function calculateStageKey($userId)
    {
        $points = $this->getUserPoints($userId);
        
        if ($points < 10) return 'egg';
        if ($points < 30) return 'caterpillar';
        if ($points < 50) return 'pupa';
        return 'butterfly';
    }
    
    /**
     * Get user's total points
     */
    private function getUserPoints($userId)
    {
        $points = 0;
        
        // Mood entries
        $moodCount = Mood::where('user_id', $userId)->count();
        $points += $moodCount;
        
        // Positive moods bonus
        $positiveMoods = Mood::where('user_id', $userId)
            ->whereIn('mood_level', [6, 7, 8, 9, 10])
            ->count();
        $points += $positiveMoods;
        
        // Completed activities (0.5 each)
        $activityCount = MoodBooster::where('user_id', $userId)->count();
        $points += $activityCount * 0.5;
        
        return round($points, 1);
    }
}