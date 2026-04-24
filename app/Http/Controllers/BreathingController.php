<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BreathingSession;

class BreathingController extends Controller
{
    public function store(Request $request)
    {
        BreathingSession::create([
            'user_id' => Auth::id(),
            'duration' => $request->duration
        ]);

        return response()->json([
            'message' => 'Breathing session saved'
        ]);
    }

    public function history()
    {
        $sessions = BreathingSession::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('breathing-mt', compact('sessions'));
    }
}