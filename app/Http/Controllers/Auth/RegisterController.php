<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    // Show signup page
    public function showRegistrationForm()
    {
        return view('layouts.auth.signup');
    }

    // Handle registration
    public function register(Request $request)
    {
        // ✅ Validate input
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        // ✅ Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // ✅ Auto login after register
        Auth::login($user);

        // ✅ Redirect
        return redirect('/chat');
    }
}
