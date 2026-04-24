@extends('layouts.app')

@section('content')

<main class="auth-body">

  <main class="auth-wrapper">
    <section class="auth-card">
      <h1 class="auth-title">Create your account</h1>
      <p class="auth-subtitle">Start your journey with Airé 🌿</p>

      <form method="POST" action="{{ url('/signup') }}" class="auth-form">
        @csrf
        @if ($errors->any())
          <div class="auth-error">
            {{ $errors->first() }}
          </div>
        @endif
        <div class="auth-field">
          <label for="name">Full Name</label>
          <input id="name" name="name" type="text" placeholder="e.g. Full Name" required />
        </div>

        <div class="auth-field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" placeholder="e.g. name@email.com" required />
        </div>

        <div class="auth-grid-2">
          <div class="auth-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Create a password" required />
          </div>

          <div class="auth-field">
            <label for="confirm">Confirm Password</label>
            <input id="confirm" name="password_confirmation" type="password" placeholder="Repeat password" required />
          </div>
        </div>

        <label class="auth-check">
          <input type="checkbox" required />
          <span>
            I agree to the 
            <a href="#" id="termsLink">Terms & Privacy Policy</a>
          </span>
        </label>

        <button type="submit" class="auth-btn">Create Account</button>

        <p class="auth-foot">
          Already have an account?
          <a href="{{ url('login') }}">Login</a>
        </p>
      </form>
    </section>

    <!-- Terms Popup -->
    <div id="termsModal" class="terms-modal hidden">
      <div class="terms-box">
        <button class="terms-close" id="closeTerms">✕</button>

        <h2>Terms & Privacy Policy</h2>

        <div class="terms-content">
          <p>
            This website is a supportive self-care tool and does not replace professional medical advice.
          </p>

          <p>
            Your data (mood logs, journal entries) are stored in a secured cloud and used to improve your experience.
          </p>

          <p>
            By using Airé, you agree to use the platform responsibly and seek professional help if needed.
          </p>
        </div>
      </div>
    </div>

  </main>

</main>

@endsection


@section('scripts')

<script src="{{ asset('js/signup.js') }}"></script>

@endsection