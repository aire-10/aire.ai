@extends('layouts.app')

@section('content')

<main class="auth-body">

  <main class="auth-wrapper">
    <section class="auth-card">
      <h1 class="auth-title">Welcome back</h1>
      <p class="auth-subtitle">Log in to continue your journey 🌿</p>

      <form method="POST" action="{{ url('/login') }}" class="auth-form">
        @csrf

        @if ($errors->any())
          <div class="auth-error">
            {{ $errors->first() }}
          </div>
        @endif

        <div class="auth-field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" placeholder="e.g. name@email.com" required />
        </div>

        <div class="auth-field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="Enter your password" required />
        </div>

        <div class="auth-row">
          <label class="auth-check">
            <input type="checkbox" />
            <span>Remember me</span>
          </label>
          
        </div>

        <button type="submit" class="auth-btn">Login</button>

        <p class="auth-foot">
          Don’t have an account?
          <a href="{{ url('signup') }}">Sign up</a>
        </p>
      </form>
    </section>
  </main>

</main>

@endsection


@section('scripts')


@endsection