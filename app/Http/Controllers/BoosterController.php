<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class BoosterController extends Controller
{
    public function get($type)
    {
        $user = auth()->user();

        if (!isset($user->$type) || !is_array($user->$type)) {
            $data = ['completed' => []];
        } else {
            $data = $user->$type;
        }

        return response()->json([
            'completed' => $data['completed'] ?? [],
            'date' => now()->format('Y-m-d')
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

        if (!isset($user->$type) || !is_array($user->$type)) {
            $data = ['completed' => []];
        } else {
            $data = $user->$type;
        }

        if (in_array($index, $data['completed'])) {
            $data['completed'] = array_values(array_diff($data['completed'], [$index]));
        } else {
            $data['completed'][] = $index;
        }

        $user->$type = $data;
        $user->save();

        return response()->json([
            'completed' => $data['completed']
        ]);
    }

    public function reset($type)
    {
        $user = auth()->user();

        $user->$type = ['completed' => []];
        $user->save();

        return response()->json(['success' => true]);
    }

    public function check($type)
    {
        $user = auth()->user();

        if (!isset($user->$type) || !is_array($user->$type)) {
            $data = ['completed' => []];
        } else {
            $data = $user->$type;
        }

        $totalTasks = [
            'moodlifting' => 6,
            'mindreset' => 5,
            'minitask' => 8,
            'bodybooster' => 4
        ];

        $required = $totalTasks[$type] ?? 0;

        $completed = count($data['completed'] ?? []) >= $required;

        return response()->json([
            'completed' => $completed
        ]);
    }
}