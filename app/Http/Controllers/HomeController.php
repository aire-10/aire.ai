<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\Journal;
use App\Models\MiniTask;
use App\Models\Grounding;
use App\Models\MoodBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['landing']);
    }
    
    /**
     * Display the landing page for non-authenticated users
     */
    public function landing()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('landing');
    }
    
    /**
     * Display the home dashboard
     */
    public function index()
    {
        return view('home');
    }
    
    /**
     * Get all dashboard data for AJAX refresh
     */
    public function getDashboardData()
    {
        $userId = Auth::id();
        
        // Get user stats
        $streak = $this->calculateStreak($userId);
        $daysTracked = $this->getDaysTracked($userId);
        $stageKey = $this->calculateStageKey($userId);
        
        // Get today's mood
        $todayMood = Mood::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->first();
        
        $latestMood = $todayMood ? $this->mapMoodLevelToName($todayMood->mood_level) : null;
        
        // Get completed tasks for today
        $todayTasksCompleted = MiniTask::where('user_id', $userId)
            ->whereDate('completed_at', today())
            ->count();
        
        // Get total tasks pending
        $pendingTasks = MiniTask::where('user_id', $userId)
            ->whereNull('completed_at')
            ->count();
        
        // Get recent journal entries
        $recentJournals = Journal::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($journal) {
                return [
                    'id' => $journal->id,
                    'title' => $journal->title,
                    'date' => $journal->created_at->toDateString()
                ];
            });
        
        // Get weekly mood data for chart
        $weeklyMoods = $this->getWeeklyMoodData($userId);
        
        // Get growth points
        $growthPoints = $this->getGrowthPoints($userId);
        
        // Get affirmation for today
        $affirmation = $this->getDailyAffirmation();
        
        return response()->json([
            'success' => true,
            'data' => [
                'streak' => $streak,
                'days_tracked' => $daysTracked,
                'stage_key' => $stageKey,
                'stage_label' => $this->getStageLabel($stageKey),
                'stage_message' => $this->getStageMessage($stageKey),
                'stage_image' => $this->getStageImage($stageKey),
                'latest_mood' => $latestMood,
                'today_tasks_completed' => $todayTasksCompleted,
                'pending_tasks' => $pendingTasks,
                'recent_journals' => $recentJournals,
                'weekly_moods' => $weeklyMoods,
                'growth_points' => $growthPoints,
                'affirmation' => $affirmation
            ]
        ]);
    }
    
    /**
     * Log mood from the home page mood check-in
     */
    public function logMood(Request $request)
    {
        $request->validate([
            'mood' => 'required|string|in:joyful,happy,neutral,sad,tired,anxious'
        ]);
        
        $userId = Auth::id();
        $moodLevel = $this->mapMoodNameToLevel($request->mood);
        
        // Create mood entry
        $mood = Mood::create([
            'user_id' => $userId,
            'mood_level' => $moodLevel,
            'notes' => 'Quick check-in from home page',
            'date' => now()->toDateString(),
            'created_at' => now()
        ]);
        
        // Get updated stats
        $streak = $this->calculateStreak($userId);
        $stageKey = $this->calculateStageKey($userId);
        $stageLabel = $this->getStageLabel($stageKey);
        $stageMessage = $this->getStageMessage($stageKey);
        $stageImage = $this->getStageImage($stageKey);
        $tip = $this->getStageTip($stageKey);
        
        // Get title based on mood
        $title = $this->getMoodToastTitle($request->mood);
        
        return response()->json([
            'success' => true,
            'message' => 'Mood logged successfully',
            'mood' => $request->mood,
            'streak' => $streak,
            'stage_key' => $stageKey,
            'stage_label' => $stageLabel,
            'stage_message' => $stageMessage,
            'stage_image' => $stageImage,
            'toast_title' => $title,
            'toast_tip' => $tip,
            'days_tracked' => $this->getDaysTracked($userId)
        ]);
    }
    
    /**
     * Get current user stats for the pet card
     */
    public function getPetStats()
    {
        $userId = Auth::id();
        
        $streak = $this->calculateStreak($userId);
        $daysTracked = $this->getDaysTracked($userId);
        $stageKey = $this->calculateStageKey($userId);
        
        return response()->json([
            'success' => true,
            'streak' => $streak,
            'days_tracked' => $daysTracked,
            'stage_key' => $stageKey,
            'stage_label' => $this->getStageLabel($stageKey),
            'stage_message' => $this->getStageMessage($stageKey),
            'stage_image' => $this->getStageImage($stageKey)
        ]);
    }
    
    /**
     * Get daily affirmation
     */
    public function getAffirmation()
    {
        return response()->json([
            'affirmation' => $this->getDailyAffirmation()
        ]);
    }
    
    /**
     * Get all affirmations list
     */
    public function getAffirmations()
    {
        $affirmations = [
            "I am allowed to grow at my own pace, just like my butterfly.",
            "I can take one small step today, and that is enough.",
            "My feelings are valid, and they will pass in time.",
            "I am learning to be gentle with myself.",
            "I deserve rest, even when I feel behind.",
            "I can breathe through this moment.",
            "I am doing the best I can with what I have today.",
            "Progress can be quiet and still meaningful."
        ];
        
        return response()->json($affirmations);
    }
    
    // ────────────────── PRIVATE HELPER METHODS ──────────────────
    
    /**
     * Get daily affirmation based on date
     */
    private function getDailyAffirmation()
    {
        $affirmations = [
            "I am allowed to grow at my own pace, just like my butterfly.",
            "I can take one small step today, and that is enough.",
            "My feelings are valid, and they will pass in time.",
            "I am learning to be gentle with myself.",
            "I deserve rest, even when I feel behind.",
            "I can breathe through this moment.",
            "I am doing the best I can with what I have today.",
            "Progress can be quiet and still meaningful."
        ];
        
        $seed = (date('Y') * 10000) + (date('m') * 100) + date('d');
        $index = $seed % count($affirmations);
        
        return $affirmations[$index];
    }
    
    /**
     * Calculate user streak (consecutive days with mood entry)
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
     * Get total days user has tracked mood
     */
    private function getDaysTracked($userId)
    {
        return Mood::where('user_id', $userId)
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->count();
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
        
        // Check for decline if butterfly was reached
        $recentMoods = Mood::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $negativeCount = $recentMoods->filter(function($mood) {
            return $mood->mood_level <= 3;
        })->count();
        
        if ($points >= 50 && $negativeCount >= 2) {
            return 'struggling';
        }
        if ($points >= 50 && $negativeCount >= 1) {
            return 'surviving';
        }
        
        return 'butterfly';
    }
    
    /**
     * Calculate total growth points
     */
    private function getGrowthPoints($userId)
    {
        $points = 0;
        
        // Mood entries: 1 point each
        $moodCount = Mood::where('user_id', $userId)->count();
        $points += $moodCount;
        
        // Positive moods: +1 extra point
        $positiveMoods = Mood::where('user_id', $userId)
            ->whereIn('mood_level', [6, 7, 8, 9, 10])
            ->count();
        $points += $positiveMoods;
        
        // Journal entries: +1 point each
        $journalCount = Journal::where('user_id', $userId)->count();
        $points += $journalCount;
        
        // Completed activities: +0.5 each
        $activityCount = MoodBooster::where('user_id', $userId)->count();
        $points += $activityCount * 0.5;
        
        // Grounding exercises: +0.5 each
        $groundingCount = Grounding::where('user_id', $userId)->count();
        $points += $groundingCount * 0.5;
        
        // Completed mini tasks: +0.5 each
        $taskCount = MiniTask::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count();
        $points += $taskCount * 0.5;
        
        return round($points, 1);
    }
    
    /**
     * Get stage label
     */
    private function getStageLabel($stageKey)
    {
        $labels = [
            'egg' => 'Egg',
            'caterpillar' => 'Caterpillar',
            'pupa' => 'Pupa',
            'butterfly' => 'Butterfly 🦋',
            'surviving' => 'Surviving',
            'struggling' => 'Struggling'
        ];
        
        return $labels[$stageKey] ?? 'Egg';
    }
    
    /**
     * Get stage message
     */
    private function getStageMessage($stageKey)
    {
        $messages = [
            'egg' => 'Your journey is just beginning. 🥚',
            'caterpillar' => 'Every step forward matters. 🐾',
            'pupa' => "You're transforming, little one. 🐛",
            'butterfly' => "Spread your wings! You're glowing. 🦋",
            'surviving' => "You're getting through this. 💚",
            'struggling' => 'One small step at a time. You\'ve got this. 🌱'
        ];
        
        return $messages[$stageKey] ?? 'Your journey continues. 🌿';
    }
    
    /**
     * Get stage image filename
     */
    private function getStageImage($stageKey)
    {
        $images = [
            'egg' => 'egg.png',
            'caterpillar' => 'caterpillar.png',
            'pupa' => 'pupa.png',
            'butterfly' => 'adult_glow.png',
            'surviving' => 'surviving.jpeg',
            'struggling' => 'struggling.jpeg'
        ];
        
        return $images[$stageKey] ?? 'egg.png';
    }
    
    /**
     * Get stage tip for toast
     */
    private function getStageTip($stageKey)
    {
        $tips = [
            'egg' => 'Log a Joyful 😄 or Happy 😊 mood to hatch your egg!',
            'caterpillar' => "Keep logging positive moods for 1–2 days to become a Caterpillar 🐛",
            'pupa' => "You're so close! 3–4 days positive gets you to Butterfly 🦋",
            'butterfly' => "Amazing! Keep your streak alive to stay a Butterfly 🦋",
            'surviving' => "Log Joyful or Happy moods for 5 days to become a Butterfly again 🌤️",
            'struggling' => "One positive mood starts your recovery. You've got this! 💚"
        ];
        
        return $tips[$stageKey] ?? '';
    }
    
    /**
     * Get toast title based on mood
     */
    private function getMoodToastTitle($mood)
    {
        $titles = [
            'joyful' => '🌟 Your butterfly is thriving!',
            'happy' => '🌿 Your butterfly is glowing!',
            'neutral' => '😐 Your butterfly is resting…',
            'sad' => '💙 Your butterfly feels your sadness.',
            'tired' => '😴 Your butterfly is tired too.',
            'anxious' => '💙 Your butterfly is with you.'
        ];
        
        return $titles[$mood] ?? '🌱 Your butterfly noticed your mood.';
    }
    
    /**
     * Get weekly mood data for chart
     */
    private function getWeeklyMoodData($userId)
    {
        $weeklyData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $mood = Mood::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->orderBy('created_at', 'desc')
                ->first();
            
            $weeklyData[] = [
                'date' => $date,
                'day' => now()->subDays($i)->format('D'),
                'mood' => $mood ? $this->mapMoodLevelToName($mood->mood_level) : null,
                'mood_level' => $mood ? $mood->mood_level : null
            ];
        }
        
        return $weeklyData;
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
    
    /**
     * Map mood name to level
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
}