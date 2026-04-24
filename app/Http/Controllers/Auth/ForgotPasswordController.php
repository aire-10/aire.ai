<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    // Show forgot password page
    public function showForgotForm()
    {
        return view('forgotpassword');
    }

    // Handle reset
    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Email not found.'
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        return redirect('/login')->with('status', 'Password updated successfully!');
    }
}