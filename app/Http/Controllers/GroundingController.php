<?php

namespace App\Http\Controllers;

use App\Models\Grounding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroundingController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }
    
    /**
     * Display the grounding exercise page
     */
    public function index()
    {
        return view('grounding');
    }
    
    /**
     * Save grounding exercise progress
     */
    public function saveProgress(Request $request)
    {
        $request->validate([
            'step_index' => 'required|integer|min:0|max:4',
            'inputs' => 'nullable|array',
            'completed_steps' => 'nullable|array',
            'is_completed' => 'boolean'
        ]);

        $userId = Auth::id() ?? 1;
        $today = now()->format('Y-m-d');

        $grounding = Grounding::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if (!$grounding) {
            $grounding = new Grounding();
            $grounding->user_id = $userId;
            $grounding->date = $today;
        }

        // ✅ FIX: SET REQUIRED FIELD
        $grounding->exercise_type = '5-4-3-2-1';

        $progress = $grounding->progress ?? [];

        // ✅ SAVE INPUTS
        if ($request->has('inputs')) {
            $progress['step_inputs'] = $request->inputs;
        }

        // ✅ HANDLE COMPLETED STEPS
        $existingSteps = $grounding->completed_steps_json ?? [];

        // start with existing
        $mergedSteps = $existingSteps;

        // merge if new steps provided
        if ($request->has('completed_steps')) {
            $incomingSteps = $request->completed_steps ?? [];

            if (is_array($incomingSteps)) {
                $mergedSteps = array_unique(array_merge($existingSteps, $incomingSteps));
                sort($mergedSteps);
            }
        }

        // ✅ ALWAYS SAVE
        $grounding->completed_steps_json = $mergedSteps;
        $progress['completed_steps'] = $mergedSteps;

        // ✅ ALWAYS UPDATE TIMESTAMP
        $progress['last_updated'] = now()->toDateTimeString();

        $grounding->progress = $progress;

        $finalSteps = $grounding->completed_steps_json ?? [];

        $grounding->completed_steps = count($finalSteps);
        $grounding->total_steps = 5;

        // ✅ AUTO COMPLETE
        if (count($finalSteps) >= 5) {
            $grounding->is_completed = true;
            $grounding->completed_at = now();
        }

        // ✅ DO NOT OVERWRITE TRUE WITH FALSE
        if ($request->has('is_completed') && $request->is_completed) {
            $grounding->is_completed = true;
            $grounding->completed_at = now();
        }

        $grounding->save();

        return response()->json([
            'success' => true,
            'completed' => $grounding->is_completed
        ]);
    }
    
    /**
     * Get today's grounding progress
     */
    public function getProgress()
    {
        $userId = Auth::id() ?? 1;
        $today = now()->format('Y-m-d');

        $grounding = Grounding::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if (!$grounding) {
            return response()->json([
                'has_progress' => false,
                'completed_steps' => [],
                'step_inputs' => [],
                'is_completed' => false
            ]);
        }

        $progress = $grounding->progress ?? [];

        return response()->json([
            'has_progress' => true,
            'completed_steps' => $grounding->completed_steps_json ?? [],
            'step_inputs' => $progress['step_inputs'] ?? [],
            'is_completed' => $grounding->is_completed,
            'completed_at' => $grounding->completed_at,
            'date' => $grounding->date,
        ]);
    }
    
    /**
     * Check if user completed grounding today
     */
    public function checkTodayCompletion()
    {
        $userId = Auth::id() ?? 1;
        $today = now()->format('Y-m-d');
        
        $completed = Grounding::where('user_id', $userId)
            ->whereDate('date', $today)
            ->where('is_completed', true)
            ->exists();
        
        return response()->json([
            'completed' => $completed,
            'date' => $today
        ]);
    }
    
    /**
     * Get grounding history/stats
     */
    public function getStats()
    {
        $userId = Auth::id() ?? 1;
        
        $totalCompletions = Grounding::where('user_id', $userId)
            ->where('is_completed', true)
            ->count();
        
        $streak = $this->calculateCurrentStreak($userId);
        $longestStreak = $this->calculateLongestStreak($userId);
        
        $recentCompletions = Grounding::where('user_id', $userId)
            ->where('is_completed', true)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->pluck('date');
        
        return response()->json([
            'total_completions' => $totalCompletions,
            'current_streak' => $streak,
            'longest_streak' => $longestStreak,
            'recent_dates' => $recentCompletions,
            'completion_rate' => $this->calculateCompletionRate($userId)
        ]);
    }
    
    /**
     * Save individual step inputs
     */
    public function saveStepInputs(Request $request)
    {
        $request->validate([
            'step_index' => 'required|integer|min:0|max:4',
            'inputs' => 'nullable|array',
            'step_completed' => 'boolean'
        ]);
        
        $userId = Auth::id() ?? 1;
        $today = now()->format('Y-m-d');
        
        $grounding = Grounding::firstOrNew([
            'user_id' => $userId,
            'date' => $today
        ]);
        
        $progress = $grounding->progress ?? [];

        if (!isset($progress['step_inputs'])) {
            $progress['step_inputs'] = [];
        }

        $progress['step_inputs'][$request->step_index] = $request->inputs;
        $progress['last_updated'] = now()->toDateTimeString();

        $grounding->progress = $progress;
        
        if ($request->step_completed) {
            $completedSteps = $grounding->completed_steps_json ?? [];
            if (!in_array($request->step_index, $completedSteps)) {
                $completedSteps[] = $request->step_index;
                $grounding->completed_steps_json = $completedSteps;
                $grounding->completed_steps = count($completedSteps);
            }
            
            // Check if all steps are completed
            if (count($completedSteps) >= 5) {
                $grounding->is_completed = true;
                $grounding->completed_at = now();
                $this->awardXP($userId, 0.5);
            }
        }
        
        $grounding->save();
        
        return response()->json([
            'success' => true,
            'completed_steps' => $grounding->completed_steps_json ?? [],
            'is_completed' => $grounding->is_completed
        ]);
    }
    
    /**
     * Reset today's progress (start over)
     */
    public function resetProgress()
    {
        $userId = Auth::id() ?? 1;
        $today = now()->format('Y-m-d');
        
        $deleted = Grounding::where('user_id', $userId)
            ->whereDate('date', $today)
            ->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Progress reset'
        ]);
    }
    
    /**
     * Get all grounding history for the user
     */
    public function getHistory(Request $request)
    {
        $userId = Auth::id() ?? 1;
        $limit = $request->get('limit', 30);
        
        $history = Grounding::where('user_id', $userId)
            ->where('is_completed', true)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->date,
                    'completed_at' => $item->completed_at,
                    'steps_completed' => $item->completed_steps
                ];
            });
        
        return response()->json($history);
    }
    
    // ────────────────── PRIVATE HELPER METHODS ──────────────────
    
    /**
     * Format step inputs for storage
     */
    private function formatStepInputs($inputs)
    {
        $formatted = [];
        foreach ($inputs as $stepIndex => $stepInputs) {
            $formatted[$stepIndex] = array_values(array_filter($stepInputs, function($value) {
                return !empty(trim($value));
            }));
        }
        return $formatted;
    }
    
    /**
     * Award XP to user for completing grounding
     */
    private function awardXP($userId, $amount)
    {
        // You can create an XP model or use session
        // For now, we'll store in a user_xp table or session
        $currentXP = session()->get('user_xp_' . $userId, 0);
        session()->put('user_xp_' . $userId, $currentXP + $amount);
        
        // Or create an XP model
        // XP::create(['user_id' => $userId, 'amount' => $amount, 'source' => 'grounding']);
        
        return true;
    }
    
    /**
     * Calculate current streak of grounding completions
     */
    private function calculateCurrentStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();
        
        $completionDates = Grounding::where('user_id', $userId)
            ->where('is_completed', true)
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
     * Calculate longest streak of grounding completions
     */
    private function calculateLongestStreak($userId)
    {
        $completionDates = Grounding::where('user_id', $userId)
            ->where('is_completed', true)
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
        
        $completions = Grounding::where('user_id', $userId)
            ->where('is_completed', true)
            ->whereIn('date', $last30Days)
            ->count();
        
        return round(($completions / 30) * 100, 1);
    }
}