<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('profile', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $user = Auth::user();

        // Update name
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // Update password
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        // Update profile picture
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile_photos', 'public');
            $user->profile_photo = $path; // make sure column exists
        }

        $user->save();

        return back()->with('success', 'Profile updated!');
    }

}