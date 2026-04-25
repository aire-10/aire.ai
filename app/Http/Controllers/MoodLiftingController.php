<?php

namespace App\Http\Controllers;

use App\Models\Mood;
use App\Models\MoodBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodLiftingController extends Controller
{
    public function get()
    {
        $user = auth()->user();

        $data = $user->mood_lifting ?? [];

        return response()->json([
            'completed' => $data['completed'] ?? []
        ]);
    }

    public function toggle(Request $req)
    {
        $user = auth()->user();
        $index = $req->index;

        $data = $user->mood_lifting ?? ['completed' => []];

        if (in_array($index, $data['completed'])) {
            $data['completed'] = array_diff($data['completed'], [$index]);
        } else {
            $data['completed'][] = $index;
        }

        $user->mood_lifting = $data;
        $user->save();

        return response()->json([
            'completed' => $data['completed']
        ]);
    }

    public function reset()
    {
        $user = auth()->user();
        $user->mood_lifting = ['completed' => []];
        $user->save();

        return response()->json(['success' => true]);
    }

    public function checkToday()
    {
        $user = auth()->user();
        $data = $user->mood_lifting ?? [];

        $completed = count($data['completed'] ?? []) >= 6;

        return response()->json([
            'completed' => $completed
        ]);
    }
}