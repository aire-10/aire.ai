<?php

namespace App\Http\Controllers;

use App\Models\MindReset;
use App\Models\MoodBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MindResetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the mind reset page
     */
    public function index()
    {
        return view('mindreset');
    }
    
    /**
     * Save progress for mind reset items
     */
    public function saveProgress(Request $request)
    {
        $request->validate([
            'completed_items' => 'required|array',
            'completed_count' => 'required|integer',
            'total_items' => 'required|integer'
        ]);
        
        $userId = Auth::id();
        $today = now()->toDateString();
        
        // Store progress in session or database
        $progressKey = 'mindreset_progress_' . $today;
        session()->put($progressKey, [
            'completed_items' => $request->completed_items,
            'completed_count' => $request->completed_count,
            'total_items' => $request->total_items,
            'updated_at' => now()->toDateTimeString()
        ]);
        
        // Check if all items are completed
        $isCompleted = $request->completed_count >= $request->total_items;
        
        if ($isCompleted) {
            // Check if already recorded today
            $alreadyCompleted = MoodBooster::where('user_id', $userId)
                ->where('booster_type', 'mindreset')
                ->whereDate('completed_at', $today)
                ->exists();
            
            if (!$alreadyCompleted) {
                // Record completion in mood_boosters table
                $mindReset = MoodBooster::create([
                    'user_id' => $userId,
                    'booster_type' => 'mindreset',
                    'activity_name' => 'Complete Mind Reset',
                    'duration' => 0,
                    'completed_at' => now()
                ]);
                
                // Also record in mind_resets table if you want separate tracking
                MindReset::create([
                    'user_id' => $userId,
                    'technique' => 'mind_reset_exercise',
                    'duration' => 5,
                    'completed' => true,
                    'started_at' => now()->subMinutes(5),
                    'completed_at' => now(),
                    'notes' => 'Completed all ' . $request->total_items . ' mind reset items'
                ]);
                
                return response()->json([
                    'success' => true,
                    'just_completed' => true,
                    'message' => 'Mind reset completed! +0.5 XP',
                    'points_awarded' => 0.5,
                    'completed_count' => $request->completed_count,
                    'total_items' => $request->total_items
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'saved' => true,
            'completed_count' => $request->completed_count,
            'total_items' => $request->total_items,
            'is_completed' => $isCompleted
        ]);
    }
    
    /**
     * Get today's mind reset progress
     */
    public function getProgress()
    {
        $today = now()->toDateString();
        $progressKey = 'mindreset_progress_' . $today;
        
        $progress = session()->get($progressKey, [
            'completed_items' => [],
            'completed_count' => 0,
            'total_items' => 6, // Default from blade template
            'has_progress' => false
        ]);
        
        $progress['has_progress'] = session()->has($progressKey);
        
        return response()->json($progress);
    }
    
    /**
     * Check if user completed mind reset today
     */
    public function checkTodayCompletion()
    {
        $userId = Auth::id();
        $today = now()->toDateString();
        
        $completed = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'mindreset')
            ->whereDate('completed_at', $today)
            ->exists();
        
        return response()->json([
            'completed' => $completed,
            'date' => $today
        ]);
    }
    
    /**
     * Get mind reset statistics
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        $totalCompletions = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'mindreset')
            ->count();
        
        $currentStreak = $this->calculateCurrentStreak($userId);
        $bestStreak = $this->calculateBestStreak($userId);
        
        $totalMinutesSpent = MindReset::where('user_id', $userId)
            ->where('completed', true)
            ->sum('duration');
        
        $recentCompletions = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'mindreset')
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
            'total_minutes' => $totalMinutesSpent,
            'recent_completions' => $recentCompletions,
            'completion_rate' => $this->calculateCompletionRate($userId)
        ]);
    }
    
    /**
     * Get the default mind reset items list
     */
    public function getItems()
    {
        $items = [
            'Take 3 deep, slow breaths',
            'Notice one thing you can smell',
            'Wash your face with cool water',
            'Let out a big sigh',
            'Unclench your jaw and drop your shoulders',
            'Close your eyes for 30 seconds'
        ];
        
        return response()->json($items);
    }
    
    /**
     * Reset today's progress
     */
    public function resetProgress()
    {
        $today = now()->toDateString();
        $progressKey = 'mindreset_progress_' . $today;
        
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
                ->where('booster_type', 'mindreset')
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
     * Get encouragement messages for mind reset
     */
    public function getEncouragementMessages()
    {
        $messages = [
            "You’re grounding yourself 🌿",
            "Good job noticing ✨",
            "Stay present, you're doing well 💚",
            "Every small reset adds up 🌱",
            "Your mind thanks you for this break 🧠",
            "You're building mental resilience 💪",
            "This is self-care in action 🫶",
            "Keep going, you're doing great! 🌟"
        ];
        
        return response()->json($messages);
    }
    
    /**
     * Start a mind reset session (for timer-based resets)
     */
    public function startSession(Request $request)
    {
        $request->validate([
            'duration' => 'nullable|integer|min:1|max:60'
        ]);
        
        $session = MindReset::create([
            'user_id' => Auth::id(),
            'technique' => 'guided_mind_reset',
            'duration' => $request->duration ?? 5,
            'started_at' => now(),
            'completed' => false
        ]);
        
        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'message' => 'Mind reset session started'
        ]);
    }
    
    /**
     * Complete a mind reset session
     */
    public function completeSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:mind_resets,id',
            'stress_before' => 'nullable|integer|min:1|max:10',
            'stress_after' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string'
        ]);
        
        $session = MindReset::findOrFail($request->session_id);
        
        // Verify ownership
        if ($session->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $session->update([
            'completed_at' => now(),
            'completed' => true,
            'stress_before' => $request->stress_before,
            'stress_after' => $request->stress_after,
            'notes' => $request->notes
        ]);
        
        // Also record in mood_boosters for XP tracking
        $today = now()->toDateString();
        $alreadyCompleted = MoodBooster::where('user_id', Auth::id())
            ->where('booster_type', 'mindreset')
            ->whereDate('completed_at', $today)
            ->exists();
        
        if (!$alreadyCompleted) {
            MoodBooster::create([
                'user_id' => Auth::id(),
                'booster_type' => 'mindreset',
                'activity_name' => 'Complete Mind Reset Session',
                'duration' => $session->duration,
                'completed_at' => now()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Mind reset session completed! +0.5 XP',
            'duration' => $session->duration,
            'stress_reduction' => $request->stress_before && $request->stress_after ? 
                $request->stress_before - $request->stress_after : null
        ]);
    }
    
    /**
     * Get all mind reset sessions history
     */
    public function getSessionHistory(Request $request)
    {
        $userId = Auth::id();
        $limit = $request->get('limit', 20);
        
        $sessions = MindReset::where('user_id', $userId)
            ->where('completed', true)
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($session) {
                return [
                    'id' => $session->id,
                    'date' => $session->completed_at->toDateString(),
                    'duration' => $session->duration,
                    'stress_before' => $session->stress_before,
                    'stress_after' => $session->stress_after,
                    'completed_at' => $session->completed_at->toISOString()
                ];
            });
        
        return response()->json($sessions);
    }
    
    // ────────────────── PRIVATE HELPER METHODS ──────────────────
    
    /**
     * Calculate current streak of mind reset completions
     */
    private function calculateCurrentStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();
        
        $completionDates = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'mindreset')
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
            ->where('booster_type', 'mindreset')
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
            ->where('booster_type', 'mindreset')
            ->whereIn(DB::raw('DATE(completed_at)'), $last30Days)
            ->count();
        
        return round(($completions / 30) * 100, 1);
    }
}