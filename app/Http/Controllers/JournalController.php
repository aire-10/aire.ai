<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    // Show journal page
    public function index()
    {
        return view('journal');
    }

    // Save journal entry (WITH IMAGE)
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('journals', 'public');
        }

        $journal = Journal::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'image' => $imagePath
        ]);

        return response()->json([
            'message' => 'Saved',
            'journal' => $journal
        ]);
    }

    // Show history
    public function history(Request $request)
    {
        $query = Journal::where('user_id', Auth::id());

        // FILTER BY MONTH
        if ($request->month && $request->month !== 'ALL') {
            $monthNumber = date('m', strtotime($request->month));
            $query->whereMonth('created_at', $monthNumber);
        }

        // FILTER BY YEAR
        if ($request->year && $request->year !== 'ALL') {
            $query->whereYear('created_at', $request->year);
        }

        $journals = $query->latest()->get();

        return view('journal-history', compact('journals'));
    }

    // Show single journal entry
    public function show($id)
    {
        $journal = Journal::where('user_id', Auth::id())
            ->findOrFail($id);

        return view('journal-detail', compact('journal'));
    }
}