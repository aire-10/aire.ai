<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BoosterController extends Controller
{
    public function get($type)
    {
        $user = auth()->user();

        $data = $user->$type ?? ['completed' => []];

        return response()->json([
            'completed' => $data['completed'] ?? []
        ]);
    }

    public function toggle(Request $req)
    {
        $type = $req->type;
        $index = $req->index;

        $user = auth()->user();

        $data = $user->$type ?? ['completed' => []];

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

        $data = $user->$type ?? ['completed' => []];

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