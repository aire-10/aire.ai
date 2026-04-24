<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\MoodBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodLiftingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the mood lifting page
     */
    public function index()
    {
        return view('moodlifting');
    }
    
    /**
     * Save progress for mood lifting thoughts
     */
    public function saveProgress(Request $request)
    {
        $request->validate([
            'completed_thoughts' => 'required|array',
            'completed_count' => 'required|integer',
            'total_thoughts' => 'required|integer'
        ]);
        
        $userId = Auth::id();
        $today = now()->toDateString();
        
        // Store progress in session or database
        $progressKey = 'moodlifting_progress_' . $today;
        session()->put($progressKey, [
            'completed_thoughts' => $request->completed_thoughts,
            'completed_count' => $request->completed_count,
            'total_thoughts' => $request->total_thoughts,
            'updated_at' => now()->toDateTimeString()
        ]);
        
        // Check if all thoughts are completed
        $isCompleted = $request->completed_count >= $request->total_thoughts;
        
        if ($isCompleted) {
            // Check if already recorded today
            $alreadyCompleted = MoodBooster::where('user_id', $userId)
                ->where('booster_type', 'moodlifting')
                ->whereDate('completed_at', $today)
                ->exists();
            
            if (!$alreadyCompleted) {
                // Record completion
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
                    'message' => 'Mood lifting completed! +0.5 XP',
                    'points_awarded' => 0.5
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'saved' => true,
            'completed_count' => $request->completed_count,
            'total_thoughts' => $request->total_thoughts,
            'is_completed' => $isCompleted
        ]);
    }
    
    /**
     * Get today's mood lifting progress
     */
    public function getProgress()
    {
        $today = now()->toDateString();
        $progressKey = 'moodlifting_progress_' . $today;
        
        $progress = session()->get($progressKey, [
            'completed_thoughts' => [],
            'completed_count' => 0,
            'total_thoughts' => 6, // Default from blade template
            'has_progress' => false
        ]);
        
        $progress['has_progress'] = session()->has($progressKey);
        
        return response()->json($progress);
    }
    
    /**
     * Check if user completed mood lifting today
     */
    public function checkTodayCompletion()
    {
        $userId = Auth::id();
        $today = now()->toDateString();
        
        $completed = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'moodlifting')
            ->whereDate('completed_at', $today)
            ->exists();
        
        return response()->json([
            'completed' => $completed,
            'date' => $today
        ]);
    }
    
    /**
     * Get mood lifting statistics
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        $totalCompletions = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'moodlifting')
            ->count();
        
        $currentStreak = $this->calculateCurrentStreak($userId);
        $bestStreak = $this->calculateBestStreak($userId);
        
        $recentCompletions = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'moodlifting')
            ->orderBy('completed_at', 'desc')
            ->limit(7)
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->completed_at->toDateString(),
                    'completed_at' => $item->completed_at->toISOString()
                ];
            });
        
        return response()->json([
            'total_completions' => $totalCompletions,
            'current_streak' => $currentStreak,
            'best_streak' => $bestStreak,
            'recent_completions' => $recentCompletions,
            'completion_rate' => $this->calculateCompletionRate($userId)
        ]);
    }
    
    /**
     * Get the default thoughts list
     */
    public function getThoughts()
    {
        $thoughts = [
            'Recall a happy memory',
            'Think of your favourite food',
            "You're still here. That's enough.",
            "What's one thing you did okay today?",
            'Tell yourself: "This feeling is okay, and it will pass."',
            "If you're tired, what's one tiny rest you can take?"
        ];
        
        return response()->json($thoughts);
    }
    
    /**
     * Reset today's progress
     */
    public function resetProgress()
    {
        $today = now()->toDateString();
        $progressKey = 'moodlifting_progress_' . $today;
        
        session()->forget($progressKey);
        
        return response()->json([
            'success' => true,
            'message' => 'Progress reset successfully'
        ]);
    }
    
    /**
     * Get weekly completion data for charts
     */
    public function getWeeklyData()
    {
        $userId = Auth::id();
        $weeklyData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $completed = MoodBooster::where('user_id', $userId)
                ->where('booster_type', 'moodlifting')
                ->whereDate('completed_at', $date)
                ->exists();
            
            $weeklyData[] = [
                'date' => $date,
                'day' => now()->subDays($i)->format('D'),
                'completed' => $completed,
                'day_name' => now()->subDays($i)->format('l')
            ];
        }
        
        return response()->json($weeklyData);
    }
    
    /**
     * Get encouragement messages
     */
    public function getEncouragementMessages()
    {
        $messages = [
            "That’s something to be proud of 💚",
            "That matters more than you think ✨",
            "You're doing better than you realise 🌿",
            "That’s a meaningful reflection 💭",
            "Every thought you cross off is a step forward 🌱",
            "Your mind is getting lighter with each one 🦋",
            "This is self-care in action 💪",
            "You're building emotional strength 💚"
        ];
        
        return response()->json($messages);
    }
    
    // ────────────────── PRIVATE HELPER METHODS ──────────────────
    
    /**
     * Calculate current streak of mood lifting completions
     */
    private function calculateCurrentStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();
        
        $completionDates = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'moodlifting')
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
     * Calculate longest streak ever
     */
    private function calculateBestStreak($userId)
    {
        $completionDates = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'moodlifting')
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
     * Calculate completion rate (last 30 days)
     */
    private function calculateCompletionRate($userId)
    {
        $last30Days = [];
        for ($i = 0; $i < 30; $i++) {
            $last30Days[] = now()->subDays($i)->toDateString();
        }
        
        $completions = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'moodlifting')
            ->whereIn(DB::raw('DATE(completed_at)'), $last30Days)
            ->count();
        
        return round(($completions / 30) * 100, 1);
    }
}