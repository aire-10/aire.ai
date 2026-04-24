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

        <form class="auth-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="auth-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="e.g. ainaa@email.com" required />
            </div>

            <button type="submit" class="auth-btn">Send Reset Link</button>

            <p class="auth-foot">
                Remember your password?
                <a href="{{ route('login') }}">Back to Login</a>
            </p>
        </form>
    </section>
</main>
@endsection