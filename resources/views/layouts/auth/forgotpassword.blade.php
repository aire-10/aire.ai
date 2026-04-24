@extends('layouts.app')

@section('title', 'Forgot Password')
@section('body-class', 'auth-body')

@section('content')
<main class="auth-wrapper">
    <section class="auth-card">
        <h1 class="auth-title">Forgot your password?</h1>
        <p class="auth-subtitle">
            Enter your email and we’ll send you a reset link.
        </p>

        <form class="auth-form" method="POST" action="{{ url('/forgot-password') }}">
            @csrf

            <div class="auth-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required />
            </div>

            <div class="auth-field">
                <label for="password">New Password</label>
                <input id="password" name="password" type="password" required />
            </div>

            <div class="auth-field">
                <label for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required />
            </div>

            <button type="submit" class="auth-btn">Reset Password</button>

            <p class="auth-foot">
                Remember your password?
                <a href="{{ route('login') }}">Back to Login</a>
            </p>
        </form>
    </section>
</main>
@endsection