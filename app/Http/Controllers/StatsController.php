<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserStat;
use App\Models\Mood;
use Carbon\Carbon;
use App\Models\Grounding;
use App\Models\MoodBooster;
use App\Models\MiniTask;
use App\Models\MindReset;

class StatsController extends Controller
{
    public function getStats()
    {
        $user = Auth::user();

        $stats = UserStat::firstOrCreate([
            'user_id' => $user->id
        ]);

        $today = Carbon::today()->toDateString();

        return response()->json([
            'points' => $stats->points,
            'streak' => $stats->streak,
            'stage' => $stats->stage,

            // ✅ ADD THESE
            'daysTracked' => Mood::where('user_id', $user->id)
                ->selectRaw('DATE(created_at) as date')
                ->distinct()
                ->count(),

            'todayCheckIns' => Mood::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count(),

            'latestMood' => optional(
                Mood::where('user_id', $user->id)
                    ->latest()
                    ->first()
            ) ? match(true) {
                optional(
                    Mood::where('user_id', $user->id)->latest()->first()
                )->mood_level >= 9 => 'joyful',

                optional(
                    Mood::where('user_id', $user->id)->latest()->first()
                )->mood_level >= 7 => 'happy',

                optional(
                    Mood::where('user_id', $user->id)->latest()->first()
                )->mood_level >= 5 => 'neutral',

                default => 'sad'
            } : null
        ]);
    }

    public function updateStats()
    {
        $user = Auth::user();

        $today = Carbon::today()->toDateString();

        $moods = Mood::where('user_id', $user->id)->get();

        // ✅ TOTAL POINTS (CUMULATIVE)
        $points = 0;

        // group moods by day
        $grouped = $moods->groupBy(function ($m) {
            return \Carbon\Carbon::parse($m->created_at)->toDateString();
        });

        $points = 0;

        foreach ($grouped as $date => $entries) {

            // +1 for logging mood that day
            $points += 1;

            // +1 bonus if ANY positive mood that day
            if ($entries->contains(fn($m) => in_array($m->mood_level, [8, 9, 10]))) {
                $points += 1;
            }
        }

        // boosters (ALL activities)
        $points += MoodBooster::where('user_id', $user->id)->count() * 0.5;

        // grounding
        $points += Grounding::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count() * 0.5;

        // ✅ STREAK (CONSECUTIVE DAYS)
        $grouped = $moods->groupBy(function ($m) {
            return \Carbon\Carbon::parse($m->created_at)->toDateString();
        })->sortKeysDesc();

        $streak = 0;
        $checkDate = Carbon::today();

        // ✅ FIX: allow yesterday as start
        if (!isset($grouped[$checkDate->toDateString()])) {
            $checkDate->subDay();
        }

        foreach ($grouped as $date => $entries) {

            if ($date !== $checkDate->toDateString()) {
                break;
            }

            $hasPositive = $entries->contains(fn($m) =>
                in_array($m->mood_level, [8, 9, 10])
            );

            if ($hasPositive) {
                $streak++;
                $checkDate->subDay();
            } else {
                break;
            }
        }

        // ✅ STAGE (MATCH growth.js LOGIC)
        if ($points >= 50) $stage = 'butterfly';
        elseif ($points >= 30) $stage = 'pupa';
        elseif ($points >= 10) $stage = 'caterpillar';
        else $stage = 'egg';

        $stats = UserStat::updateOrCreate(
            ['user_id' => $user->id],
            [
                'points' => $points,
                'streak' => $streak,
                'stage' => $stage
            ]
        );

        return response()->json($stats);
    }
}