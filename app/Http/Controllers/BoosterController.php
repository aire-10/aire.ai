<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class BoosterController extends Controller
{
    public function get($type)
    {
        $user = auth()->user();

        $data = $user->$type ?? [];

        $today = now()->toDateString();

        // ✅ RESET DAILY
        if (!isset($data['date']) || $data['date'] !== $today) {
            $data = [
                'completed' => [],
                'date' => $today
            ];
        }

        return response()->json([
            'completed' => $data['completed'],
            'date' => $data['date']
        ]);
    }

    public function toggle(Request $req)
    {
        $type = $req->type;
        $index = $req->index;

        $allowed = ['moodlifting', 'mindreset', 'minitask', 'bodybooster'];

        if (!in_array($type, $allowed)) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $user = auth()->user();

        $data = $user->$type ?? [];

        $today = now()->toDateString();

        // ✅ RESET DAILY
        if (!isset($data['date']) || $data['date'] !== $today) {
            $data = [
                'completed' => [],
                'date' => $today
            ];
        }

        // toggle step
        if (in_array($index, $data['completed'])) {
            $data['completed'] = array_values(array_diff($data['completed'], [$index]));
        } else {
            $data['completed'][] = $index;
        }

        $user->$type = $data;
        $user->save();

        // ✅ TASK COUNT
        $totalTasks = [
            'moodlifting' => 6,
            'mindreset' => 5,
            'minitask' => 8,
            'bodybooster' => 4
        ];

        $required = $totalTasks[$type] ?? 0;

        // ✅ IF FULLY COMPLETED → SAVE + UPDATE POINTS
        if (count($data['completed']) >= $required) {

            $exists = \App\Models\MoodBooster::where('user_id', $user->id)
                ->where('booster_type', $type)
                ->whereDate('completed_at', $today)
                ->exists();

            if (!$exists) {
                \App\Models\MoodBooster::create([
                    'user_id' => $user->id,
                    'booster_type' => $type,
                    'completed_at' => now()
                ]);

                // ✅ ONLY update when new completion happens
                app(\App\Http\Controllers\StatsController::class)->updateStats();
            }
        }

        return response()->json([
            'completed' => $data['completed']
        ]);
    }

    public function check($type)
    {
        $user = auth()->user();

        $data = $user->$type ?? [];

        $today = now()->toDateString();

        // ✅ RESET DAILY
        if (!isset($data['date']) || $data['date'] !== $today) {
            return response()->json(['completed' => false]);
        }

        $totalTasks = [
            'moodlifting' => 6,
            'mindreset' => 5,
            'minitask' => 8,
            'bodybooster' => 4
        ];

        $required = $totalTasks[$type] ?? 0;

        return response()->json([
            'completed' => count($data['completed']) >= $required
        ]);
    }

    public function reset($type)
    {
        $allowed = ['moodlifting', 'mindreset', 'minitask', 'bodybooster'];

        if (!in_array($type, $allowed)) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $user = auth()->user();

        // 🔥 RESET ONLY FRONTEND STATE (NOT DB RECORDS)
        $user->$type = [
            'completed' => [],
            'date' => now()->toDateString()
        ];

        $user->save();

        return response()->json([
            'success' => true
        ]);
    }

}