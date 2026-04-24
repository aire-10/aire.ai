<?php

namespace App\Http\Controllers;

use App\Models\MiniTask;
use App\Models\MoodBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MiniTaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the mini tasks page
     */
    public function index()
    {
        return view('minitask');
    }
    
    /**
     * Save progress for mini tasks
     */
    public function saveProgress(Request $request)
    {
        $request->validate([
            'completed_tasks' => 'required|array',
            'completed_count' => 'required|integer',
            'total_tasks' => 'required|integer'
        ]);
        
        $userId = Auth::id();
        $today = now()->toDateString();
        
        // Store progress in session
        $progressKey = 'minitask_progress_' . $today;
        session()->put($progressKey, [
            'completed_tasks' => $request->completed_tasks,
            'completed_count' => $request->completed_count,
            'total_tasks' => $request->total_tasks,
            'updated_at' => now()->toDateTimeString()
        ]);
        
        // Check if all tasks are completed
        $isCompleted = $request->completed_count >= $request->total_tasks;
        
        if ($isCompleted) {
            // Check if already recorded today
            $alreadyCompleted = MoodBooster::where('user_id', $userId)
                ->where('booster_type', 'minitask')
                ->whereDate('completed_at', $today)
                ->exists();
            
            if (!$alreadyCompleted) {
                // Record completion
                MoodBooster::create([
                    'user_id' => $userId,
                    'booster_type' => 'minitask',
                    'activity_name' => 'Complete Mini Tasks',
                    'duration' => 0,
                    'completed_at' => now()
                ]);
                
                // Also save individual tasks to mini_tasks table
                foreach ($request->completed_tasks as $taskIndex) {
                    MiniTask::create([
                        'user_id' => $userId,
                        'title' => $this->getTaskTitle($taskIndex),
                        'description' => 'Completed from mini tasks page',
                        'priority' => 'medium',
                        'completed_at' => now()
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'just_completed' => true,
                    'message' => 'Mini tasks completed! +0.5 XP',
                    'points_awarded' => 0.5,
                    'completed_count' => $request->completed_count,
                    'total_tasks' => $request->total_tasks
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'saved' => true,
            'completed_count' => $request->completed_count,
            'total_tasks' => $request->total_tasks,
            'is_completed' => $isCompleted
        ]);
    }
    
    /**
     * Get today's mini tasks progress
     */
    public function getProgress()
    {
        $today = now()->toDateString();
        $progressKey = 'minitask_progress_' . $today;
        
        $progress = session()->get($progressKey, [
            'completed_tasks' => [],
            'completed_count' => 0,
            'total_tasks' => 2, // Default from blade template
            'has_progress' => false
        ]);
        
        $progress['has_progress'] = session()->has($progressKey);
        
        return response()->json($progress);
    }
    
    /**
     * Check if user completed mini tasks today
     */
    public function checkTodayCompletion()
    {
        $userId = Auth::id();
        $today = now()->toDateString();
        
        $completed = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'minitask')
            ->whereDate('completed_at', $today)
            ->exists();
        
        return response()->json([
            'completed' => $completed,
            'date' => $today
        ]);
    }
    
    /**
     * Get mini tasks statistics
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        $totalCompletions = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'minitask')
            ->count();
        
        $currentStreak = $this->calculateCurrentStreak($userId);
        $bestStreak = $this->calculateBestStreak($userId);
        
        $totalTasksCompleted = MiniTask::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count();
        
        $recentCompletions = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'minitask')
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
            'total_tasks_completed' => $totalTasksCompleted,
            'recent_completions' => $recentCompletions,
            'completion_rate' => $this->calculateCompletionRate($userId)
        ]);
    }
    
    /**
     * Get the default mini tasks list
     */
    public function getTasks()
    {
        $tasks = [
            ['text' => 'Take 3 slow breaths', 'duration' => 15],
            ['text' => 'Stretch your neck gently', 'duration' => 15]
        ];
        
        return response()->json($tasks);
    }
    
    /**
     * Reset today's progress
     */
    public function resetProgress()
    {
        $today = now()->toDateString();
        $progressKey = 'minitask_progress_' . $today;
        
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
                ->where('booster_type', 'minitask')
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
     * Get encouragement messages for mini tasks
     */
    public function getEncouragementMessages()
    {
        $messages = [
            "Nice job 💚",
            "Small steps matter 🌱",
            "Keep going ✨",
            "You're building momentum 💪",
            "Every task completed is a win 🎯",
            "Your future self thanks you 🌟",
            "That's one more step forward 🦋",
            "You're doing great! 🫶"
        ];
        
        return response()->json($messages);
    }
    
    /**
     * Get task title by index
     */
    private function getTaskTitle($index)
    {
        $tasks = [
            0 => 'Take 3 slow breaths',
            1 => 'Stretch your neck gently'
        ];
        
        return $tasks[$index] ?? 'Mini Task';
    }
    
    /**
     * Calculate current streak of mini tasks completions
     */
    private function calculateCurrentStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();
        
        $completionDates = MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'minitask')
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
            ->where('booster_type', 'minitask')
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
            ->where('booster_type', 'minitask')
            ->whereIn(DB::raw('DATE(completed_at)'), $last30Days)
            ->count();
        
        return round(($completions / 30) * 100, 1);
    }
}