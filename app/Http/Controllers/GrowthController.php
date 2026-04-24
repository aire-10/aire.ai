<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\Journal;
use App\Models\Grounding;
use App\Models\MoodBooster;
use App\Models\MiniTask;
use App\Models\MindReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrowthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $userId = Auth::id();
        
        // Get mood log (same structure AireData expects)
        $moodLog = $this->getMoodLog($userId);
        
        // Get today's completed actions for checkmarks
        $today = now()->toDateString();
        $completedActions = [
            'journal' => $this->hasJournalToday($userId, $today),
            'mood' => $this->hasMoodToday($userId, $today),
            'grounding' => $this->hasGroundingToday($userId, $today),
            'bodybooster' => $this->hasBodyBoosterToday($userId, $today),
            'minitask' => $this->hasMiniTaskToday($userId, $today),
            'mindreset' => $this->hasMindResetToday($userId, $today),
            'moodlifting' => $this->hasMoodLiftingToday($userId, $today),
        ];
        
        // Get streak
        $streak = $this->calculateStreak($userId);
        
        // Get days tracked
        $daysTracked = $this->getDaysTracked($userId);
        
        // Get today's check-in count
        $todayCheckIns = $this->getTodayCheckInCount($userId, $today);
        
        // Get latest mood
        $latestMood = $this->getLatestMood($userId);
        
        // Get growth points (cumulative)
        $growthPoints = $this->getGrowthPoints($userId);
        
        // Calculate stage based on points
        $stage = $this->calculateStage($growthPoints, $streak);
        
        return response()->json([
            'moodLog' => $moodLog,
            'completedActions' => $completedActions,
            'streak' => $streak,
            'daysTracked' => $daysTracked,
            'todayCheckIns' => $todayCheckIns,
            'latestMood' => $latestMood,
            'growthPoints' => $growthPoints,
            'stage' => $stage,
            'positiveMoods' => ['joyful', 'happy', 'content', 'calm', 'grateful'],
            'moodMeta' => $this->getMoodMeta()
        ]);
    }
    
    // Get mood log in the format AireData expects
    private function getMoodLog($userId)
    {
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
        
        return $log;
    }
    
    // Map numeric mood level to mood name
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
    
    // Map mood name to level (for storage)
    private function mapMoodNameToLevel($name)
    {
        $mapping = [
            'joyful' => 9,
            'happy' => 8,
            'content' => 6,
            'neutral' => 5,
            'tired' => 3,
            'anxious' => 3,
            'sad' => 2
        ];
        
        return $mapping[$name] ?? 5;
    }
    
    // Get mood metadata for frontend
    private function getMoodMeta()
    {
        return [
            'joyful' => ['emoji' => '😊', 'label' => 'Joyful', 'color' => '#3a8c3a'],
            'happy' => ['emoji' => '🙂', 'label' => 'Happy', 'color' => '#5aab5a'],
            'content' => ['emoji' => '😌', 'label' => 'Content', 'color' => '#7aab72'],
            'neutral' => ['emoji' => '😐', 'label' => 'Neutral', 'color' => '#b0a060'],
            'tired' => ['emoji' => '😴', 'label' => 'Tired', 'color' => '#c47a5a'],
            'anxious' => ['emoji' => '😰', 'label' => 'Anxious', 'color' => '#c47a5a'],
            'sad' => ['emoji' => '😢', 'label' => 'Sad', 'color' => '#a07070']
        ];
    }
    
    // Check if user wrote journal today
    private function hasJournalToday($userId, $today)
    {
        return Journal::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->exists();
    }
    
    // Check if user logged mood today
    private function hasMoodToday($userId, $today)
    {
        return Mood::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->exists();
    }
    
    // Check if user did grounding exercise today
    private function hasGroundingToday($userId, $today)
    {
        return Grounding::where('user_id', $userId)
            ->whereDate('completed_at', $today)
            ->exists();
    }
    
    // Check if user did body booster today
    private function hasBodyBoosterToday($userId, $today)
    {
        return MoodBooster::where('user_id', $userId)
            ->whereDate('completed_at', $today)
            ->exists();
    }
    
    // Check if user completed mini task today
    private function hasMiniTaskToday($userId, $today)
    {
        return MiniTask::where('user_id', $userId)
            ->whereDate('completed_at', $today)
            ->exists();
    }
    
    // Check if user did mind reset today
    private function hasMindResetToday($userId, $today)
    {
        return MindReset::where('user_id', $userId)
            ->whereDate('completed_at', $today)
            ->where('completed', true)
            ->exists();
    }
    
    // Check if user did mood lifting activity today
    private function hasMoodLiftingToday($userId, $today)
    {
        // You can create a mood_lifting_activities table or use session
        // For now, check if they have a mood entry with notes containing activity
        return Mood::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->whereNotNull('notes')
            ->exists();
    }
    
    // Calculate current streak
    private function calculateStreak($userId)
    {
        $streak = 0;
        $currentDate = now()->toDateString();
        
        // Get all mood entries grouped by date
        $moodDates = Mood::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();
        
        // Check consecutive days
        $checkDate = $currentDate;
        while (in_array($checkDate, $moodDates)) {
            $streak++;
            $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
        }
        
        return $streak;
    }
    
    // Get total days user has tracked mood
    private function getDaysTracked($userId)
    {
        return Mood::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'))
            ->distinct()
            ->count();
    }
    
    // Get today's check-in count
    private function getTodayCheckInCount($userId, $today)
    {
        return Mood::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->count();
    }
    
    // Get latest mood
    private function getLatestMood($userId)
    {
        $latest = Mood::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($latest) {
            return $this->mapMoodLevelToName($latest->mood_level);
        }
        
        return null;
    }
    
    // Calculate total growth points
    private function getGrowthPoints($userId)
    {
        $points = 0;
        
        // Journal entries: +1 point per journal
        $journalCount = Journal::where('user_id', $userId)->count();
        $points += $journalCount;
        
        // Positive moods: +1 point per positive mood
        $positiveMoods = Mood::where('user_id', $userId)
            ->whereIn('mood_level', [6, 7, 8, 9, 10])
            ->count();
        $points += $positiveMoods;
        
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
        
        // Mind resets: +0.5 points each
        $resetCount = MindReset::where('user_id', $userId)
            ->where('completed', true)
            ->count();
        $points += $resetCount * 0.5;
        
        return round($points, 1);
    }
    
    // Calculate stage based on points and streak
    private function calculateStage($points, $streak)
    {
        // Check for decline (negative moods)
        $recentMoods = Mood::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $negativeCount = $recentMoods->filter(function($mood) {
            return $mood->mood_level <= 3;
        })->count();
        
        $hasReachedButterfly = $points >= 50;
        
        if ($hasReachedButterfly && $negativeCount >= 2) {
            return [
                'stageKey' => 'struggling',
                'label' => 'Struggling 💔',
                'img' => 'struggling.jpeg',
                'color' => '#a07070',
                'xpMax' => 2,
                'recoveryNeeded' => 2
            ];
        } elseif ($hasReachedButterfly && $negativeCount >= 1) {
            return [
                'stageKey' => 'surviving',
                'label' => 'Surviving 🌱',
                'img' => 'surviving.jpeg',
                'color' => '#b0a060',
                'xpMax' => 3,
                'recoveryNeeded' => 3
            ];
        } elseif ($points >= 50) {
            return [
                'stageKey' => 'butterfly',
                'label' => 'Butterfly 🦋',
                'img' => 'adult_glow.png',
                'color' => '#3a8c3a',
                'xpMax' => 100
            ];
        } elseif ($points >= 30) {
            return [
                'stageKey' => 'pupa',
                'label' => 'Pupa 🦋',
                'img' => 'pupa.png',
                'color' => '#4c7a60',
                'xpMax' => 50
            ];
        } elseif ($points >= 10) {
            return [
                'stageKey' => 'caterpillar',
                'label' => 'Caterpillar 🐛',
                'img' => 'caterpillar.png',
                'color' => '#7aab72',
                'xpMax' => 30
            ];
        } else {
            return [
                'stageKey' => 'egg',
                'label' => 'Egg 🥚',
                'img' => 'egg.png',
                'color' => '#8a7060',
                'xpMax' => 10
            ];
        }
    }
    
    // API endpoint to record an action completion
    public function recordAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:journal,mood,grounding,bodybooster,minitask,mindreset,moodlifting'
        ]);
        
        $userId = Auth::id();
        $today = now()->toDateString();
        
        // Store completion status in session or database
        // For now, we'll store in session (or you can create a user_actions table)
        $completed = session()->get('completed_actions', []);
        $completed[$request->action] = $today;
        session()->put('completed_actions', $completed);
        
        // Recalculate growth points
        $points = $this->getGrowthPoints($userId);
        
        return response()->json([
            'success' => true,
            'message' => 'Action recorded!',
            'points' => $points,
            'stage' => $this->calculateStage($points, $this->calculateStreak($userId))
        ]);
    }
    
    // Get all growth data for the frontend (AJAX endpoint)
    public function getData()
    {
        $userId = Auth::id();
        
        return response()->json([
            'moodLog' => $this->getMoodLog($userId),
            'streak' => $this->calculateStreak($userId),
            'daysTracked' => $this->getDaysTracked($userId),
            'todayCheckIns' => $this->getTodayCheckInCount($userId, now()->toDateString()),
            'latestMood' => $this->getLatestMood($userId),
            'growthPoints' => $this->getGrowthPoints($userId),
            'stage' => $this->calculateStage(
                $this->getGrowthPoints($userId),
                $this->calculateStreak($userId)
            ),
            'completedActions' => [
                'journal' => $this->hasJournalToday($userId, now()->toDateString()),
                'mood' => $this->hasMoodToday($userId, now()->toDateString()),
                'grounding' => $this->hasGroundingToday($userId, now()->toDateString()),
                'bodybooster' => $this->hasBodyBoosterToday($userId, now()->toDateString()),
                'minitask' => $this->hasMiniTaskToday($userId, now()->toDateString()),
                'mindreset' => $this->hasMindResetToday($userId, now()->toDateString()),
                'moodlifting' => $this->hasMoodLiftingToday($userId, now()->toDateString()),
            ]
        ]);
    }
    
    // Get mood chart data
    public function getMoodChartData()
    {
        $userId = Auth::id();
        $days = 14;
        
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $mood = Mood::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $chartData[] = [
                'date' => $date,
                'mood' => $mood ? $this->mapMoodLevelToName($mood->mood_level) : null,
                'score' => $mood ? $mood->mood_level : null
            ];
        }
        
        return response()->json($chartData);
    }
}