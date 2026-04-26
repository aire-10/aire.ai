<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('layouts.auth.signup');
    }

    public function register(Request $request)
    {
        // Validate
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Login the user
        Auth::login($user);

        // Regenerate session
        $request->session()->regenerate();

        // Check if user is logged in
        if (Auth::check()) {
            // Redirect to home
            return redirect('/home');
        }

        // Fallback - still redirect to home
        return redirect('/home');
    }
}