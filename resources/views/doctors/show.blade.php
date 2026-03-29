@extends('layouts.app')

@php
    $specialty  = $doctor->specialty?->name ?? 'Specialist Doctor';
    $location   = $doctor->location?->name  ?? 'Bangladesh';
    $safeImg    = str_replace('\\', '/', $doctor->image_path ?? '');
    $imgUrl     = $safeImg ? (Str::startsWith($safeImg, 'http') ? $safeImg : asset($safeImg)) : null;
    $docUrl     = route('doctor.show', ['idslug' => $doctor->id . '-' . Str::slug($doctor->name)]);
    $ogImg      = $imgUrl ?? asset('logo.png');

    // Build JSON-LD as a PHP string to avoid Blade @if nesting inside @section
    $chambers = [];
    foreach ($doctor->chambers as $ch) {
        $c = [
            '@type'        => 'MedicalClinic',
            'name'         => $ch->hospital->name ?? 'Private Chamber',
            'address'      => $ch->address ?? '',
            'openingHours' => $ch->visiting_hour ?? '',
        ];
        if ($ch->appointment_number) $c['telephone'] = $ch->appointment_number;
        $chambers[] = $c;
    }

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'Physician',
        'name'            => $doctor->name,
        'description'     => $doctor->degrees . ($doctor->designation ? ' – ' . $doctor->designation : ''),
        '@id'             => $docUrl,
        'url'             => $docUrl,
        'medicalSpecialty'=> $specialty,
        'address'         => ['@type' => 'PostalAddress', 'addressLocality' => $location, 'addressCountry' => 'BD'],
    ];
    if ($imgUrl) $schema['image'] = $imgUrl;
    if (!empty($chambers)) {
        $schema['hasMap'] = $docUrl;
        $schema['availableService'] = $chambers;
    }
    $schemaJson = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

@section('title',            "{$doctor->name} - {$specialty} in {$location} | eHealthFinder")
@section('meta_description', "{$doctor->name} ({$doctor->degrees}) is a {$specialty} in {$location}. Find chamber address, visiting hours, and appointment number on eHealthFinder.")
@section('meta_keywords',    "{$doctor->name}, {$specialty} in {$location}, {$specialty} Bangladesh, doctor {$location}, {$doctor->degrees}")
@section('canonical',        $docUrl)
@section('og_type',          'profile')
@section('og_title',         "{$doctor->name} | {$specialty} in {$location}")
@section('og_description',   "{$doctor->name} – {$doctor->degrees}. Book appointment and find chamber details.")
@section('og_image',         $ogImg)

@section('schema')
<script type="application/ld+json">{!! $schemaJson !!}</script>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a> ›
    <a href="{{ route('doctors.index') }}">Doctors</a> ›
    @if($doctor->specialty)<a href="{{ route('doctors.index', ['specialty_id' => $doctor->specialty_id]) }}">{{ $specialty }}</a> ›@endif
    <span>{{ $doctor->name }}</span>
</div>

<div class="profile-hero">
    <img src="{{ $imgUrl ?? 'https://ui-avatars.com/api/?name='.urlencode($doctor->name).'&background=4f46e5&color=fff&size=200' }}"
         class="profile-img" alt="{{ $doctor->name }} - {{ $specialty }} in {{ $location }}">
    <div class="profile-info">
        <h1>{{ $doctor->name }}</h1>
        <p class="pdesig">{{ $doctor->degrees }}</p>
        <div class="profile-pills">
            @if($doctor->specialty)<span class="profile-pill">🩺 {{ $specialty }}</span>@endif
            @if($doctor->location)<span class="profile-pill">📍 {{ $location }}</span>@endif
            @if($doctor->experience)<span class="profile-pill">⏱ {{ $doctor->experience }}</span>@endif
        </div>
        @if($doctor->designation || $doctor->workplace)
        <div style="margin-top: 1.5rem; font-size: 0.98rem; color: rgba(255,255,255,0.85); line-height: 1.8;">
            @if($doctor->designation)<div>🏥 <strong>{{ $doctor->designation }}</strong></div>@endif
            @if($doctor->workplace)<div>🏨 {{ $doctor->workplace }}</div>@endif
        </div>
        @endif
    </div>
</div>

@if($doctor->about_text)
<div class="info-card">
    <h2>About Dr. {{ Str::before($doctor->name, ' ') }}</h2>
    <div style="line-height: 1.8; color: var(--text); font-size: 0.98rem;">
        {!! nl2br(e($doctor->about_text)) !!}
    </div>
</div>
@endif

<h2 style="margin-bottom: 1.5rem; font-size: 1.5rem;">Chamber & Appointment</h2>
<div class="grid-3" style="margin-bottom: 3rem;">
    @forelse($doctor->chambers as $chamber)
        <div class="chamber-card">
            <div class="chamber-header">
                <h3>{{ $chamber->hospital->name ?? 'Private Chamber' }}</h3>
                @if(isset($chamber->hospital->location))
                    <span style="font-size:0.8rem; color:#0369a1;">📍 {{ $chamber->hospital->location->name }}</span>
                @endif
            </div>
            <div class="chamber-body">
                <div class="chamber-row">
                    <span>📍</span>
                    <span>{{ $chamber->address }}</span>
                </div>
                <div class="chamber-row">
                    <span>🕒</span>
                    <span>{{ $chamber->visiting_hour }}</span>
                </div>
                @if($chamber->appointment_number)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $chamber->appointment_number) }}" class="call-btn">
                    📞 {{ $chamber->appointment_number }}
                </a>
                @endif
            </div>
        </div>
    @empty
        <p style="color: var(--text-light);">No chamber information available yet.</p>
    @endforelse
</div>
@endsection
