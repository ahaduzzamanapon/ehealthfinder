@extends('layouts.app')

@php
    $specialty  = $doctor->specialty?->name ?? 'Specialist Doctor';
    $location   = $doctor->location?->name  ?? 'Bangladesh';
    $safeImg    = str_replace('\\', '/', $doctor->image_path ?? '');
    $imgUrl     = $safeImg ? (Str::startsWith($safeImg, 'http') ? $safeImg : asset($safeImg)) : null;
    $docUrl     = route('doctor.show', ['idslug' => $doctor->seo_slug]);
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
<style>
.mdx-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
    padding: 3rem 2.5rem;
    border-radius: 1.5rem;
    margin-bottom: 3rem;
    color: white;
    display: flex;
    gap: 2.5rem;
    align-items: center;
    box-shadow: 0 10px 30px rgba(29, 78, 216, 0.15);
}
@media(max-width: 768px) {
    .mdx-hero { flex-direction: column; text-align: center; gap: 1.5rem; padding: 2.5rem 1.5rem;}
}
.mdx-img {
    width: 160px; height: 160px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.2);
    object-fit: cover;
    background: #e2e8f0;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.mdx-name {
    font-size: 2.4rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    color: white;
    line-height: 1.2;
}
.mdx-deg {
    font-size: 1.15rem;
    color: #e0e7ff;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}
.mdx-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    padding: 0.5rem 1.2rem;
    border-radius: 50px;
    font-size: 0.95rem;
    font-weight: 700;
    margin-right: 0.6rem;
    margin-bottom: 0.6rem;
    border: 1px solid rgba(255,255,255,0.1);
}
.mdx-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);
}
.mdx-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 1rem;
}
.mdx-chamber-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}
.mdx-chamber-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.8rem;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
}
.mdx-chamber-card:hover {
    border-color: #1d4ed8;
    box-shadow: 0 10px 20px -5px rgba(29, 78, 216, 0.1);
    transform: translateY(-4px);
}
.mdx-btn {
    display: inline-block;
    width: 100%;
    text-align: center;
    background: #eff6ff;
    color: #1d4ed8;
    padding: 0.9rem;
    border-radius: 10px;
    font-weight: 800;
    text-decoration: none;
    margin-top: auto;
    border: 1px solid #bfdbfe;
    transition: all 0.2s;
}
.mdx-btn:hover { background: #1d4ed8; color: white; border-color: #1d4ed8; }
</style>

<div class="breadcrumb" style="margin-bottom: 2rem;">
    <a href="{{ route('home') }}">Home</a> ›
    <a href="{{ route('doctors.index') }}">Doctors</a> ›
    @if($doctor->specialty)<a href="{{ \App\Helpers\SeoHelper::getSeoUrl($doctor->specialty_id) }}">{{ $specialty }}</a> ›@endif
    <span style="color: #64748b;">{{ $doctor->name }}</span>
</div>

<div class="mdx-hero">
    <img src="{{ $imgUrl ?? 'https://ui-avatars.com/api/?name='.urlencode($doctor->name).'&background=4f46e5&color=fff&size=200' }}"
         class="mdx-img" alt="{{ $doctor->name }} - {{ $specialty }} in {{ $location }}">
    <div class="mdx-info">
        <h1 class="mdx-name">{{ $doctor->name }}</h1>
        <p class="mdx-deg">{{ $doctor->degrees }}</p>
        <div>
            @if($doctor->specialty)<span class="mdx-pill">🩺 {{ $specialty }}</span>@endif
            @if($doctor->location)<span class="mdx-pill">📍 {{ $location }}</span>@endif
            @if($doctor->experience)<span class="mdx-pill">⏱ {{ $doctor->experience }}</span>@endif
        </div>
        @if($doctor->designation || $doctor->workplace)
        <div style="margin-top: 1.5rem; font-size: 1.05rem; color: rgba(255,255,255,0.9); line-height: 1.6;">
            @if($doctor->designation)<div style="margin-bottom:0.4rem;"><strong>👨‍💼 {{ $doctor->designation }}</strong></div>@endif
            @if($doctor->workplace)<div>🏥 {{ $doctor->workplace }}</div>@endif
        </div>
        @endif
    </div>
</div>

@if($doctor->about_text)
<div class="mdx-card">
    <h2 class="mdx-title">ℹ️ About Dr. {{ Str::before($doctor->name, ' ') }}</h2>
    <div style="line-height: 1.8; color: #475569; font-size: 1.05rem;">
        {!! nl2br(e($doctor->about_text)) !!}
    </div>
</div>
@endif

<div class="mdx-card" style="background: transparent; border: none; box-shadow: none; padding: 0;">
    <h2 class="mdx-title" style="border:none; padding:0;">📅 Chamber & Appointments</h2>
    <div class="mdx-chamber-grid">
        @forelse($doctor->chambers as $chamber)
            <div class="mdx-chamber-card">
                <div style="margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 0.3rem;">
                        {{ $chamber->hospital->name ?? 'Private Chamber' }}
                    </h3>
                    @if(isset($chamber->hospital->location))
                        <span style="font-size:0.9rem; font-weight: 600; color:#1d4ed8; background: #eff6ff; padding: 0.2rem 0.6rem; border-radius: 6px;">
                            {{ $chamber->hospital->location->name }}
                        </span>
                    @endif
                </div>
                
                <div style="display:flex; flex-direction: column; gap: 0.8rem; margin-bottom: 1.5rem; color: #475569; font-size: 0.95rem;">
                    <div style="display:flex; gap: 0.5rem; align-items: flex-start;">
                        <span>📍</span>
                        <span style="line-height: 1.4;">{{ $chamber->address }}</span>
                    </div>
                    <div style="display:flex; gap: 0.5rem; align-items: flex-start;">
                        <span>🕒</span>
                        <span style="line-height: 1.4;">{{ $chamber->visiting_hour }}</span>
                    </div>
                </div>

                @if($chamber->appointment_number)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $chamber->appointment_number) }}" class="mdx-btn">
                    📞 Call: {{ $chamber->appointment_number }}
                </a>
                @else
                <div style="margin-top:auto;"></div>
                @endif
            </div>
        @empty
            <div style="grid-column: 1/-1; background: white; padding: 3rem; text-align: center; border-radius: 16px; border: 1px solid #e2e8f0;">
                <p style="color: #64748b; font-size: 1.1rem;">No chamber information available yet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
