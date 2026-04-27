<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BreathingSession;

class BreathingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'duration' => 'required|integer',
            'cycles' => 'required|integer'
        ]);

        $session = BreathingSession::create([
            'user_id' => Auth::id(),
            'duration' => $request->duration,
            'cycles' => $request->cycles
        ]);

        \Log::info('Breathing session saved', [
            'user_id' => Auth::id(),
            'cycles' => $request->cycles,
            'duration' => $request->duration
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Breathing session saved successfully',
            'cycles' => $request->cycles
        ]);
    }

    public function history()
    {
        $sessions = BreathingSession::where('user_id', Auth::id())
            ->latest()
            ->get();

        $totalCycles = $sessions->sum('cycles');

        return view('breathing-mt', compact('sessions', 'totalCycles'));
    }

    // ✅ This sums cycles per day
    public function getWeeklyCycles()
    {
        $userId = Auth::id();
        $weeklyCycles = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            
            $cycles = BreathingSession::where('user_id', $userId)
                ->whereDate('created_at', $date)
                ->sum('cycles');  // ← This sums ALL cycles for that day
            
            $weeklyCycles[] = $cycles;
        }

        \Log::info('Weekly cycles data', $weeklyCycles);
        
        return response()->json($weeklyCycles);
    }
}