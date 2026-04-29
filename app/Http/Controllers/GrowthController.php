<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\Journal;
use App\Models\Grounding;
use App\Models\MoodBooster;
use App\Models\MiniTask;
use App\Models\MindReset;
use App\Models\UserStat;
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
        
        // Get growth points and stage
        $stats = UserStat::firstOrCreate([
            'user_id' => $userId
        ]);

        $streak = $stats->streak;
        $growthPoints = $stats->points;
        $stage = [
            'stageKey' => $stats->stage
        ];

        // Get days tracked
        $daysTracked = $this->getDaysTracked($userId);
        
        // Get today's check-in count
        $todayCheckIns = $this->getTodayCheckInCount($userId, $today);
        
        // Get latest mood
        $latestMood = $this->getLatestMood($userId);
        
        
        return view('growth', [
                'moodLog' => $moodLog,
                'completedActions' => $completedActions,
                'streak' => $streak,
                'daysTracked' => $daysTracked,
                'todayCheckIns' => $todayCheckIns,
                'latestMood' => $latestMood,
                'growthPoints' => $growthPoints,
                'stage' => $stage,
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
            ->where('booster_type', 'bodybooster')
            ->whereDate('completed_at', $today)
            ->exists();
    }
    
    // Check if user completed mini task today
    private function hasMiniTaskToday($userId, $today)
    {
        return MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'minitask')
            ->whereDate('completed_at', $today)
            ->exists();
    }
    
    // Check if user did mind reset today
    private function hasMindResetToday($userId, $today)
    {
        return MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'mindreset')
            ->whereDate('completed_at', $today)
            ->exists();
    }
    
    // Check if user did mood lifting activity today
    private function hasMoodLiftingToday($userId, $today)
    {
        return MoodBooster::where('user_id', $userId)
            ->where('booster_type', 'moodlifting')
            ->whereDate('completed_at', $today)
            ->exists();
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
        $stats = \App\Models\UserStat::firstOrCreate([
            'user_id' => $userId
        ]);

        $points = $stats->points;
        
        return response()->json([
            'success' => true,
            'message' => 'Action recorded!',
            'points' => $points,
            'stage' => [
                'stageKey' => $stats->stage
            ]
        ]);
    }
    
    // Get all growth data for the frontend (AJAX endpoint)
    public function getData()
    {
        $userId = Auth::id();

        // ✅ MOVE THIS HERE
        $stats = \App\Models\UserStat::firstOrCreate([
            'user_id' => $userId
        ]);

        return response()->json([
            'moodLog' => $this->getMoodLog($userId),
            'streak' => $stats->streak,
            'daysTracked' => $this->getDaysTracked($userId),
            'todayCheckIns' => $this->getTodayCheckInCount($userId, now()->toDateString()),
            'latestMood' => $this->getLatestMood($userId),

            // ✅ USE STATS
            'growthPoints' => $stats->points,
            'stage' => [
                'stageKey' => $stats->stage
            ],

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