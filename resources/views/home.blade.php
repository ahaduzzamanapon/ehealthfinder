@extends('layouts.app')

@section('title', 'eHealthFinder | Find Specialist Doctors & Medicines in Bangladesh')
@section('meta_description', 'Find verified specialist doctors in Dhaka, Chittagong, Rangpur, Sylhet and across Bangladesh. Search medicine prices, generic info, and book appointments on eHealthFinder.')
@section('meta_keywords', 'specialist doctor Bangladesh, find doctor online, doctor Dhaka, medicine price Bangladesh, book doctor appointment, ehealthfinder, specialist doctor near me')
@section('og_title', 'eHealthFinder | Find Specialist Doctors & Medicines in Bangladesh')
@section('og_description', 'Search thousands of verified specialist doctors and medicines across Bangladesh. Find chamber details, visiting hours, and appointment numbers.')
@section('og_image', asset('logo.png'))
@section('og_type', 'website')

@php
$_homeSchema = json_encode([
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'           => 'WebSite',
            'name'            => 'eHealthFinder',
            'url'             => url('/'),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => route('doctors.index') . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@type'       => 'MedicalOrganization',
            'name'        => 'eHealthFinder Bangladesh',
            'url'         => url('/'),
            'logo'        => asset('logo.png'),
            'description' => "Bangladesh's leading healthcare portal for finding specialist doctors and medicine information.",
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp

@section('schema')
<script type="application/ld+json">{!! $_homeSchema !!}</script>
@endsection

@section('content')

<style>
/* Medexly Theme Inspired Core Variables */
:root {
    --mx-blue: #1d4ed8;
    --mx-blue-light: #eff6ff;
    --mx-blue-dark: #1e3a8a;
    --text-main: #1e293b;
    --text-muted: #64748b;
    --bg-light: #f8fafc;
}

body {
    background-color: var(--bg-light);
    overflow-x: hidden;
}

@keyframes slideGrid {
    0% { background-position: 0 0, 0 0, 0 0; }
    100% { background-position: 0 0, 40px 40px, 40px 40px; }
}
@keyframes fadeInUp {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* Hero Section */
.mx-hero {
    position: relative;
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
    margin-top: -2rem; /* Negate main container top margin (Mobile Default) */
    background: #ffffff;
    background-image: 
        radial-gradient(circle at top center, rgba(67, 56, 202, 0.08) 0%, transparent 70%),
        linear-gradient(to right, rgba(29, 78, 216, 0.05) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(29, 78, 216, 0.05) 1px, transparent 1px);
    background-size: 100% 100%, 40px 40px, 40px 40px;
    padding: 7rem 2rem 6.5rem;
    text-align: center;
    color: var(--text-main);
    border-bottom: 1px solid #f1f5f9;
    animation: slideGrid 20s linear infinite;
    overflow: hidden;
}

/* Desktop override based on user request */
@media(min-width: 768px) {
    .mx-hero {
        margin-top: -8rem;
    }
}
.mx-hero::before {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 60vw; height: 60vw;
    background: radial-gradient(circle, rgba(59,130,246,0.06) 0%, transparent 60%);
    pointer-events: none;
    z-index: 0;
    animation: pulseGlow 5s ease-in-out infinite alternate;
}
@keyframes pulseGlow {
    0% { transform: translate(-50%, -50%) scale(1); opacity: 0.8; }
    100% { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }
}
.mx-hero::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0; height: 150px;
    background: linear-gradient(to top, #f8fafc, transparent);
    pointer-events: none;
}
.mx-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    padding: 0.5rem 1.2rem;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-main);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    position: relative;
    z-index: 2;
    opacity: 0;
    animation: fadeInUp 0.8s ease-out forwards;
}
.mx-hero h1 {
    font-size: 4rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    color: #0f172a;
    line-height: 1.15;
    position: relative;
    z-index: 2;
    opacity: 0;
    animation: fadeInUp 0.8s ease-out 0.2s forwards;
}
.mx-hero h1 span {
    color: #4338ca; /* Matches Medexly's vibrant blue text */
}
.mx-hero p {
    font-size: 1.35rem;
    color: #475569;
    max-width: 800px;
    margin: 0 auto 3rem;
    position: relative;
    z-index: 2;
    opacity: 0;
    animation: fadeInUp 0.8s ease-out 0.4s forwards;
}
.mx-hero p strong { color: #4338ca; }

/* Smart Search */
.mx-search-wrapper {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
    background: white;
    padding: 0.5rem 0.5rem 0.5rem 1rem;
    border-radius: 50px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    z-index: 20;
    border: 1px solid #e2e8f0;
    opacity: 0;
    animation: fadeInUp 0.8s ease-out 0.6s forwards;
}
.mx-search-icon {
    font-size: 1.5rem;
    margin-left: 1rem;
    color: var(--text-muted);
}
.mx-search-input {
    flex: 1;
    border: none;
    outline: none;
    padding: 1.2rem 1rem;
    font-size: 1.1rem;
    color: var(--text-main);
    background: transparent;
}
.mx-search-btn {
    background: #4338ca;
    color: white;
    border: none;
    padding: 1.1rem 2.5rem;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background 0.3s;
}
.mx-search-btn:hover { background: #3730a3; }

/* Pillars Grid */
.mx-pillars {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.5rem;
    max-width: 1100px;
    margin: -3rem auto 5rem;
    padding: 0 1rem;
    position: relative;
    z-index: 10;
}
.mx-pillar-card {
    background: white;
    padding: 1.5rem;
    border-radius: 1rem;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    color: var(--text-main);
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 1px solid #f1f5f9;
}
.mx-pillar-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    border-color: var(--mx-blue-light);
}
.mx-pillar-icon {
    font-size: 2.5rem;
    margin-bottom: 0.8rem;
}
.mx-pillar-title {
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
}
.mx-pillar-subtitle {
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* Section Titles */
.mx-section-title {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-main);
    margin-bottom: 0.5rem;
}
.mx-section-subtitle {
    font-size: 1rem;
    color: var(--text-muted);
    margin-bottom: 2rem;
}

/* Cards & Layouts */
.mx-grid-4 { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }
.mx-grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }

.mx-loc-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    text-decoration: none;
    color: var(--text-main);
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
.mx-loc-card:hover {
    border-color: var(--mx-blue);
    background: var(--mx-blue-light);
    transform: translateY(-2px);
}
.mx-loc-title { font-weight: 700; font-size: 1.1rem; }
.mx-loc-sub { font-size: 0.85rem; color: var(--text-muted); }

.mx-spec-card {
    background: white;
    border-radius: 12px;
    padding: 1.2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    text-decoration: none;
    color: var(--text-main);
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #f1f5f9;
    transition: all 0.2s;
}
.mx-spec-card:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border-color: var(--mx-blue);
}
.mx-spec-icon-box {
    width: 50px; height: 50px;
    background: var(--mx-blue-light);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: var(--mx-blue);
}
.mx-spec-title { font-weight: 700; font-size: 1rem; margin-bottom: 0.2rem;}
.mx-spec-count { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }

.mx-doc-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    transition: transform 0.2s;
    text-decoration: none;
    color: inherit;
    border: 1px solid #f1f5f9;
}
.mx-doc-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
.mx-doc-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border-bottom: 1px solid #f1f5f9; }
.mx-doc-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; background: #e2e8f0; }
.mx-doc-info-title { font-weight: 800; font-size: 1.15rem; color: var(--mx-blue); margin-bottom: 0.2rem; }
.mx-doc-info-deg { font-size: 0.85rem; color: var(--text-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.mx-doc-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 0.8rem; }
.mx-doc-spec { display: inline-block; background: var(--mx-blue-light); color: var(--mx-blue); padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700; }

.mx-btn-outline {
    display:inline-block; padding:0.8rem 2rem; background:white; border:2px solid var(--text-muted); color:var(--text-main); font-weight:700; border-radius:50px; text-decoration:none; transition:0.2s;
}
.mx-btn-outline:hover { background:var(--bg-light); color:var(--mx-blue); border-color:var(--mx-blue); }

@media(max-width: 768px) {
    .mx-hero { padding: 3rem 1rem 6rem; margin-bottom: -3rem; }
    .mx-hero h1 { font-size: 2rem; }
    .mx-search-wrapper { border-radius: 16px; padding: 0.5rem; flex-direction: column; gap: 0.5rem; text-align: left; }
    .mx-search-icon { display: none; }
    .mx-search-input { width: 100%; padding: 0.8rem; text-align: center; }
    .mx-search-btn { width: 100%; border-radius: 12px; }
}
</style>

<!-- MEDEXLY STYLE HERO -->
<div class="mx-hero">
    <div class="mx-hero-badge">
        <span style="width:8px;height:8px;background:#22c55e;border-radius:50%;display:inline-block;"></span>
        Bangladesh's #1 Healthcare Platform
    </div>

    <h1>Find Your Perfect<br><span>Healthcare Provider</span></h1>
    <p>eHealthFinder — Connect with <strong>{{ number_format($stats['doctors'] ?? 0) }}+ specialist doctors</strong> and access comprehensive medicine information across Bangladesh.</p>

    <!-- SMART GLOBAL SEARCH -->
    <div id="smart-search-wrap" class="mx-search-wrapper">
        <span class="mx-search-icon">🔍</span>
        <input
            id="smart-search-input"
            class="mx-search-input"
            type="text"
            autocomplete="off"
            placeholder="Search doctors, hospitals, specialties..."
        />
        <button class="mx-search-btn" onclick="document.getElementById('smart-search-form').submit()">Search &rarr;</button>
        
        <form id="smart-search-form" action="{{ route('doctors.index') }}" method="GET" style="display:none">
            <input type="hidden" id="smart-q" name="q">
            <input type="hidden" id="smart-spec" name="specialty_id">
            <input type="hidden" id="smart-loc" name="location_id">
        </form>
        <div id="smart-dropdown" style="display:none; position:absolute; top:calc(100% + 12px); left:0; right:0; background:white; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.15); z-index:1000; overflow-y:auto; max-height:400px; border:1px solid #e2e8f0; text-align: left;"></div>
    </div>
</div>

<!-- QUICK PILLARS -->
<div class="mx-pillars">
    <a href="{{ route('doctors.index') }}" class="mx-pillar-card">
        <div class="mx-pillar-icon">👨‍⚕️</div>
        <div class="mx-pillar-title">Doctors</div>
        <div class="mx-pillar-subtitle">{{ number_format($stats['doctors'] ?? 0) }}+ Registered</div>
    </a>
    <a href="{{ route('doctors.index') }}" class="mx-pillar-card">
        <div class="mx-pillar-icon">🏥</div>
        <div class="mx-pillar-title">Specialists</div>
        <div class="mx-pillar-subtitle">{{ number_format($stats['specialties'] ?? 0) }}+ Categories</div>
    </a>
    <a href="{{ route('medicines.index') }}" class="mx-pillar-card">
        <div class="mx-pillar-icon">💊</div>
        <div class="mx-pillar-title">Medicines</div>
        <div class="mx-pillar-subtitle">{{ number_format($stats['medicines'] ?? 0) }}+ Brands</div>
    </a>
    <a href="{{ route('doctors.index') }}" class="mx-pillar-card">
        <div class="mx-pillar-icon">📍</div>
        <div class="mx-pillar-title">Locations</div>
        <div class="mx-pillar-subtitle">{{ number_format($stats['locations'] ?? 0) }}+ Cities</div>
    </a>
    <a href="{{ route('blog.index') }}" class="mx-pillar-card">
        <div class="mx-pillar-icon">📰</div>
        <div class="mx-pillar-title">Health Blog</div>
        <div class="mx-pillar-subtitle">Expert Advice</div>
    </a>
</div>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; padding-bottom: 5rem;">

    <!-- DIVISIONS / LOCATIONS -->
    <div style="margin-bottom: 5rem;">
        <h2 class="mx-section-title">Connect with healthcare near you</h2>
        <p class="mx-section-subtitle">Find doctors and medical professionals in your division and city.</p>
        
        <div class="mx-grid-4">
            @foreach($locations as $loc)
                <a href="{{ \App\Helpers\SeoHelper::getSeoUrl(null, $loc->id) }}" class="mx-loc-card">
                    <span class="mx-loc-title">Doctors in {{ $loc->name }}</span>
                    <span class="mx-loc-sub">🗺️ {{ $loc->name }} এর সকল ডাক্তার</span>
                    <span style="margin-top:0.5rem; color:var(--mx-blue); font-weight:700; font-size:0.9rem;">View Doctors &rarr;</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- SPECIALTIES -->
    <div style="margin-bottom: 5rem;">
        <h2 class="mx-section-title">Find the Right Specialist for Your Care</h2>
        <p class="mx-section-subtitle">Browse doctors by their medical specialty and expertise.</p>
        
        <div class="mx-grid-4">
            @foreach($specialties->take(16) as $spec)
                <a href="{{ \App\Helpers\SeoHelper::getSeoUrl($spec->id, null) }}" class="mx-spec-card">
                    <div class="mx-spec-icon-box">🩺</div>
                    <div>
                        <div class="mx-spec-title">{{ $spec->name }}</div>
                        <div class="mx-spec-count">{{ $spec->doctors_count ?? 0 }} Verified Doctors</div>
                    </div>
                </a>
            @endforeach
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('doctors.index') }}" class="mx-btn-outline">View All Specialists</a>
        </div>
    </div>

    <!-- LATEST REGISTERED DOCTORS -->
    <div style="margin-bottom: 5rem;">
        <h2 class="mx-section-title">Latest Registered Doctors</h2>
        <p class="mx-section-subtitle">Discover newly registered healthcare professionals on our platform.</p>

        <div class="mx-grid-3">
            @foreach($featuredDoctors as $doc)
                <a href="{{ route('doctor.show', ['idslug' => $doc->seo_slug]) }}" class="mx-doc-card">
                    <div class="mx-doc-header">
                        @php $safeImg = str_replace('\\', '/', $doc->image_path); @endphp
                        <img src="{{ Str::startsWith($safeImg, 'http') ? $safeImg : ($safeImg ? asset($safeImg) : 'https://ui-avatars.com/api/?name='.urlencode($doc->name).'&background=1d4ed8&color=fff') }}" class="mx-doc-img" alt="{{ $doc->name }}">
                        <div>
                            <div class="mx-doc-info-title">{{ $doc->name }}</div>
                            <div class="mx-doc-info-deg">{{ $doc->degrees }}</div>
                        </div>
                    </div>
                    <div class="mx-doc-body">
                        <div>
                            @if($doc->specialty)<span class="mx-doc-spec">{{ $doc->specialty->name }}</span>@endif
                        </div>
                        <div style="color:var(--text-main); font-size:0.95rem;">
                            <strong>👨‍💼 {{ $doc->designation ?? 'Consultant' }}</strong><br>
                            <span style="color:var(--text-muted)">📍 {{ $doc->location->name ?? 'Bangladesh' }}</span>
                        </div>
                        <div style="margin-top: 0.5rem; text-align:center; padding:0.8rem; background:var(--mx-blue-light); color:var(--mx-blue); border-radius:8px; font-weight:700;">
                            Book Appointment
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('doctors.index') }}" class="mx-btn-outline">View All Doctors</a>
        </div>
    </div>

    <!-- POPULAR MEDICINES -->
    <div style="margin-bottom: 5rem;">
        <h2 class="mx-section-title">Essential Medicines Directory</h2>
        <p class="mx-section-subtitle">Browse popular drug brands, generic info, and updated pricing.</p>

        <div class="mx-grid-4">
            @foreach($featuredBrands as $brand)
                <a href="{{ route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug]) }}" style="background:white; border:1px solid #e2e8f0; border-radius:12px; padding:1.2rem; text-decoration:none; display:flex; flex-direction:column; gap:0.5rem; transition:transform 0.2s;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                        <span style="font-weight:800; color:var(--text-main); font-size:1.1rem;">{{ $brand->name }}</span>
                        <span style="font-size:0.75rem; background:#fee2e2; color:#dc2626; padding:2px 6px; border-radius:4px; font-weight:700;">{{ $brand->dosage_form }}</span>
                    </div>
                    <span style="font-size:0.85rem; color:var(--text-muted);"><span style="color:var(--mx-blue);">🧬</span> {{ $brand->generic->name ?? 'N/A' }}</span>
                    <span style="font-size:0.85rem; color:var(--text-muted);"><span style="color:var(--mx-blue);">🏢</span> {{ $brand->company }}</span>
                    <div style="margin-top:auto; font-weight:800; color:#10b981; font-size:1.1rem;">{{ $brand->price }}</div>
                </a>
            @endforeach
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('medicines.index') }}" class="mx-btn-outline">Browse All Medicines</a>
        </div>
    </div>

    <!-- LATEST BLOG POSTS -->
    @if(isset($latestPosts) && $latestPosts->count() > 0)
    <div style="margin-bottom: 3rem;">
        <h2 class="mx-section-title">Expert Medical Advice & Health Tips</h2>
        <p class="mx-section-subtitle">Get the latest health information and wellness guides.</p>

        <div class="mx-grid-3">
            @foreach($latestPosts as $post)
                <a href="{{ url('/' . $post->slug) }}" class="mx-doc-card" style="display:flex; flex-direction:column;">
                    <div style="height: 180px; background: #e2e8f0; position: relative; overflow: hidden;">
                        @if($post->featured_image)
                            <img src="{{ Storage::url($post->featured_image) }}" style="width:100%; height:100%; object-fit:cover; transition:transform 0.4s;" alt="{{ $post->title }}">
                        @else
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; font-size:3rem; color:#94a3b8;">📰</div>
                        @endif
                        @if($post->category)
                            <span style="position:absolute; top:1rem; right:1rem; background:var(--mx-blue); color:white; padding:4px 12px; border-radius:50px; font-size:0.75rem; font-weight:700;">{{ $post->category->name }}</span>
                        @endif
                    </div>
                    <div style="padding:1.5rem; flex:1; display:flex; flex-direction:column;">
                        <h3 style="font-size:1.2rem; font-weight:800; color:var(--text-main); margin:0 0 0.5rem 0; line-height:1.4;">{{ $post->title }}</h3>
                        <p style="color:var(--text-muted); font-size:0.9rem; margin:0 0 1rem 0; line-height:1.5; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                            {{ $post->excerpt ?? Str::limit(strip_tags($post->sections->first()->content ?? ''), 120) }}
                        </p>
                        <div style="margin-top:auto; padding-top:1rem; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">{{ $post->created_at->format('M d, Y') }}</span>
                            <span style="font-size:0.9rem; font-weight:700; color:var(--mx-blue);">Read More &rarr;</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="{{ route('blog.index') }}" class="mx-btn-outline">View All Articles</a>
        </div>
    </div>
    @endif

</div>

<!-- SMART SEARCH JAVASCRIPT LOGIC (RETAINED) -->
<script>
(function() {
    const input    = document.getElementById('smart-search-input');
    const dropdown = document.getElementById('smart-dropdown');
    const specHid  = document.getElementById('smart-spec');
    const locHid   = document.getElementById('smart-loc');
    const qHid     = document.getElementById('smart-q');
    const form     = document.getElementById('smart-search-form');
    let timer;

    const typeIcons = { combo: '📍', doctor: '👨‍⚕️', medicine: '💊', generic: '🧬' };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const val = input.value.trim();
        if (val.length < 2) { dropdown.style.display = 'none'; return; }
        timer = setTimeout(() => fetchSuggestions(val), 280);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const active = dropdown.querySelector('.ss-item.active');
            if (active) { active.click(); return; }
            qHid.value = input.value;
            specHid.value = '';
            locHid.value = '';
            form.submit();
        }
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            const items = [...dropdown.querySelectorAll('.ss-item')];
            const idx = items.findIndex(el => el.classList.contains('active'));
            items.forEach(el => el.classList.remove('active'));
            const next = e.key === 'ArrowDown' ? (idx + 1) % items.length : (idx - 1 + items.length) % items.length;
            if (items[next]) items[next].classList.add('active');
        }
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#smart-search-wrap')) dropdown.style.display = 'none';
    });

    async function fetchSuggestions(q) {
        try {
            const res  = await fetch(`/api/suggest/combined?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            renderDropdown(data, q);
        } catch(e) { dropdown.style.display = 'none'; }
    }

    function renderDropdown(items, q) {
        if (!items.length) { dropdown.style.display = 'none'; return; }
        dropdown.innerHTML = items.map((item, i) => `
            <div class="ss-item" data-url="${item.url}" data-spec="${item.specialty_id||''}" data-loc="${item.location_id||''}" data-q="${item.label}"
                 style="display:flex; align-items:center; gap:0.8rem; padding:0.9rem 1.2rem; cursor:pointer; border-bottom:1px solid #f1f5f9; transition:background 0.15s;">
                <span style="font-size:1.3rem; color:var(--text-muted);">${typeIcons[item.type]||'🔍'}</span>
                <div style="flex:1">
                    <div style="font-weight:700; color:var(--text-main); font-size:0.95rem;">${highlight(item.label, q)}</div>
                    <div style="font-size:0.8rem; color:var(--text-muted);">${item.sub||''}</div>
                </div>
                <span style="font-size:0.75rem; background:${item.type==='combo'?'#eff6ff':(item.type==='doctor'?'#f0fdf4':'#fefce8')}; color:${item.type==='combo'?'#1d4ed8':(item.type==='doctor'?'#166534':'#854d0e')}; padding:2px 10px; border-radius:12px; font-weight:700;">
                    ${item.type==='combo' ? 'Area & Specialty' : (item.type==='doctor' ? 'Profile' : 'Medicine')}
                </span>
            </div>
        `).join('');
        dropdown.style.display = 'block';

        dropdown.querySelectorAll('.ss-item').forEach(el => {
            el.addEventListener('mouseenter', () => {
                dropdown.querySelectorAll('.ss-item').forEach(x => x.classList.remove('active'));
                el.classList.add('active');
            });
            el.addEventListener('click', () => {
                const url = el.dataset.url;
                window.location.href = url;
            });
        });

        const style = document.getElementById('ss-style') || (() => {
            const s = document.createElement('style');
            s.id = 'ss-style';
            document.head.appendChild(s);
            return s;
        })();
        style.textContent = '.ss-item.active, .ss-item:hover { background: var(--mx-blue-light) !important; }';
    }

    function highlight(text, q) {
        const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
        return text.replace(re, '<mark style="background:#dbeafe;color:var(--mx-blue);border-radius:3px;padding:0 2px">$1</mark>');
    }
})();
</script>

@endsection
