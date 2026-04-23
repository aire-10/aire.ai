@extends('layouts.app')

@section('title', 'Airé — Grounding Exercise')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/grounding.css') }}">
@endpush

@section('content')
<a href="{{ route('home') }}" class="btn-back">&#8592; Back to Home</a>

<div class="grounding-container">
    <img src="{{ asset('assets/breathing leaf.png') }}" alt="" class="butterfly-top-right">
    <h1 class="grounding-title">Grounding Exercise</h1>

    <div class="grounding-layout">
        <div class="method-card">
            <p class="method-title">5-4-3-2-1 method</p>
            <div class="method-progress" id="method-progress">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <p class="progress-label" id="progress-label">0 / 5 complete</p>
        </div>

        <div class="steps-list">
            @php
                $steps = [
                    ['label' => '5 Things you see', 'placeholder' => 'I see...', 'count' => 5],
                    ['label' => '4 Things you feel', 'placeholder' => 'I feel...', 'count' => 4],
                    ['label' => '3 Things you hear', 'placeholder' => 'I hear...', 'count' => 3],
                    ['label' => '2 Things you smell', 'placeholder' => 'I smell...', 'count' => 2],
                    ['label' => '1 Thing you like', 'placeholder' => 'I like...', 'count' => 1],
                ];
            @endphp

            @foreach ($steps as $index => $step)
            <div class="step-btn" id="step-{{ $index }}" onclick="toggleStep({{ $index }})">
                <span class="step-icon">🌿</span>
                <span class="step-label">{{ $step['label'] }}</span>
                <span class="step-arrow" id="arrow-{{ $index }}">›</span>
            </div>
            <div class="step-panel" id="panel-{{ $index }}">
                <div class="step-inputs" id="inputs-{{ $index }}">
                    @for ($i = 0; $i < $step['count']; $i++)
                        <input type="text" placeholder="{{ $step['placeholder'] }}" class="step-input">
                    @endfor
                </div>
                <button class="btn-done-step" onclick="doneStep(event, {{ $index }})">Done ✓</button>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection