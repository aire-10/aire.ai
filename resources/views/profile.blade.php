@extends('layouts.app')

@section('title', 'Airé • Profile')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endpush

@section('content')
<section class="profile-hero">
    <div class="profile-hero-inner">
        <h1 class="profile-title">My Profile</h1>

        <div class="profile-grid">
            <div class="profile-card account-block">
                <h2 class="profile-card-title">Account Details</h2>
                <div class="profile-photo">
                    <img 
                        id="profilePhotoImg"
                        src="{{ $user->profile_photo 
                            ? asset('storage/' . $user->profile_photo) 
                            : asset('assets/profile.jpeg') }}"
                    >
                </div>

                <div class="account-section">
                    <div class="section-label">Current Details</div>

                    <div class="readonly-row">
                        <span class="ro-label">Name</span>
                        <span class="ro-value">{{ $user->name }}</span>
                    </div>

                    <div class="readonly-row">
                        <span class="ro-label">Email</span>
                        <span class="ro-value">{{ $user->email }}</span>
                    </div>
                </div>
                
                <form id="updateProfileForm" class="account-section" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="section-label">Update Details</div>

                    <!-- Name -->
                    <label class="field">
                        <span class="field-label">New Name</span>
                        <input id="updName" name="name" type="text" placeholder="Enter new name" />
                    </label>

                    <!-- Password -->
                    <label class="field">
                        <span class="field-label">New Password</span>
                        <input type="password" name="password" placeholder="Enter new password" />
                    </label>

                    <!-- Profile Photo -->
                    <label class="field">
                        <span class="field-label">Profile Photo</span>
                        <input type="file" name="photo" accept="image/*" />
                    </label>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-filled profile-btn">Update Profile</button>
                </form>
            </div>

            <div class="profile-card">
                <h2 class="profile-card-title">Recent Chat History</h2>
                <div id="recentChatList" class="recent-chat-list"></div>
                <a class="btn btn-filled profile-btn" href="{{ route('history') }}">View All Chats</a>
            </div>
        </div>
    </div>
</section>
@endsection