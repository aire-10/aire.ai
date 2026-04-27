@extends('layouts.app')

@section('content')

<style>
    /* Terms Modal Popup Styles */
    .terms-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    
    .terms-modal.hidden {
        display: none;
    }
    
    .terms-box {
        background: white;
        width: 90%;
        max-width: 450px;
        border-radius: 16px;
        padding: 24px 28px;
        position: relative;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .terms-close {
        position: absolute;
        top: 12px;
        right: 16px;
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
        color: #999;
        transition: color 0.2s;
    }
    
    .terms-close:hover {
        color: #333;
    }
    
    .terms-box h2 {
        margin: 0 0 16px 0;
        font-size: 20px;
        color: #2c4d3b;
        font-weight: 700;
    }
    
    .terms-content {
        font-size: 14px;
        line-height: 1.6;
        color: #444;
    }
    
    .terms-content p {
        margin-bottom: 14px;
    }
    
    .auth-error {
        background: rgba(220, 53, 69, 0.15);
        color: #c0392b;
        padding: 10px 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        font-size: 14px;
        border-left: 4px solid #c0392b;
    }
</style>

<main class="auth-body">

    <main class="auth-wrapper">
        <section class="auth-card">
            <h1 class="auth-title">Create your account</h1>
            <p class="auth-subtitle">Start your journey with Airé 🌿</p>

            <form method="POST" action="{{ url('/signup') }}" class="auth-form" id="signupForm">
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
                        <a href="javascript:void(0);" onclick="document.getElementById('termsModal').classList.remove('hidden');">Terms & Privacy Policy</a>
                    </span>
                </label>

                <button type="submit" class="auth-btn">Create Account</button>

                <p class="auth-foot">
                    Already have an account?
                    <a href="{{ url('login') }}">Login</a>
                </p>
            </form>
        </section>

        <!-- Terms Popup Modal -->
        <div id="termsModal" class="terms-modal hidden">
            <div class="terms-box">
                <button class="terms-close" onclick="document.getElementById('termsModal').classList.add('hidden');">✕</button>

                <h2>Terms & Privacy Policy</h2>

                <div class="terms-content">
                    <p>
                        This website is a supportive self-care tool and does not replace professional medical advice.
                    </p>

                    <p>
                        Your data (mood logs, journal entries) are stored in a secured database.
                    </p>

                    <p>
                        By using Airé, you agree to use the platform responsibly and seek professional help if needed. Please contact Talian Harapan:145, and Medical Emergency Hotline: 991.
                    </p>
                </div>
            </div>
        </div>

    </main>

</main>

<script>
    // Close modal when clicking outside the box
    document.getElementById('termsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
</script>

@endsection

@section('scripts')
<script src="{{ asset('js/signup.js') }}"></script>
@endsection