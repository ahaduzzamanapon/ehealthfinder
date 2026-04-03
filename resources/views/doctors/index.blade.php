@extends('layouts.app')

@php
    $spec = $selectedSpecialty->name ?? null;
    $loc  = $selectedLocation->name  ?? null;
    $q    = request('q');

    // ── Build dynamic title ──────────────────────────────────
    if ($spec && $loc) {
        $seoTitle = "{$spec} in {$loc} | Best {$spec} Doctors in {$loc} | eHealthFinder";
        $seoH1    = "{$spec} in {$loc}";
    } elseif ($spec) {
        $seoTitle = "Best {$spec} in Bangladesh | Find {$spec} Doctors | eHealthFinder";
        $seoH1    = "Best {$spec} Doctors in Bangladesh";
    } elseif ($loc) {
        $seoTitle = "Best Doctors in {$loc} | Find Specialist Doctors in {$loc} | eHealthFinder";
        $seoH1    = "Specialist Doctors in {$loc}";
    } elseif ($q) {
        $seoTitle = "Doctors for \"{$q}\" | eHealthFinder";
        $seoH1    = "Search Results for \"{$q}\"";
    } else {
        $seoTitle = "Find Specialist Doctors in Bangladesh | eHealthFinder";
        $seoH1    = "Doctor Directory";
    }

    // ── Build dynamic description ───────────────────────────
    $total = $doctors->total();
    if ($spec && $loc) {
        $seoDesc = "Find {$total} verified {$spec} doctors in {$loc}, Bangladesh. View chamber address, visiting hours, appointment numbers and book your consultation on eHealthFinder.";
    } elseif ($spec) {
        $seoDesc = "Browse {$total} verified {$spec} specialists across Bangladesh. Compare qualifications, location, and availability. Book appointments on eHealthFinder.";
    } elseif ($loc) {
        $seoDesc = "Find {$total} verified specialist doctors in {$loc}. Filter by specialty, view chamber details and appointment numbers. eHealthFinder Bangladesh.";
    } elseif ($q) {
        $seoDesc = "Found {$total} doctors matching \"{$q}\" in Bangladesh. View profiles, specialties, chambers and book appointments on eHealthFinder.";
    } else {
        $seoDesc = "Browse {$total} verified specialist doctors across Bangladesh. Filter by city and specialty to find the right doctor and book your appointment.";
    }

    // ── Build dynamic keywords ──────────────────────────────
    if ($spec && $loc) {
        $seoKeys = implode(', ', [
            "{$spec} in {$loc}",
            "best {$spec} {$loc}",
            "{$spec} doctor {$loc} Bangladesh",
            "find {$spec} in {$loc}",
            "{$spec} specialist {$loc}",
            "{$loc} {$spec} appointment",
            "specialist doctor {$loc}",
            "doctor {$loc} Bangladesh",
        ]);
    } elseif ($spec) {
        $seoKeys = implode(', ', [
            "best {$spec} in Bangladesh",
            "{$spec} doctor Bangladesh",
            "{$spec} specialist Bangladesh",
            "find {$spec} Bangladesh",
            "{$spec} appointment Bangladesh",
            "{$spec} doctor Dhaka",
            "{$spec} doctor Chittagong",
            "specialist {$spec}",
        ]);
    } elseif ($loc) {
        $seoKeys = implode(', ', [
            "best doctor in {$loc}",
            "specialist doctor {$loc}",
            "find doctor {$loc} Bangladesh",
            "doctor appointment {$loc}",
            "{$loc} specialist doctor",
            "{$loc} hospital doctor",
            "best specialist {$loc}",
            "doctor {$loc}",
        ]);
    } else {
        $seoKeys = 'specialist doctor Bangladesh, find doctor online, doctor appointment Bangladesh, best doctor dhaka, ehealthfinder, specialist doctor near me';
    }

    // ── Canonical URL ───────────────────────────────────────
    $seoCanonical = \App\Helpers\SeoHelper::getSeoUrl(request('specialty_id'), request('location_id'));

    // ── Generate JSON-LD ItemList Schema ────────────────────
    $schemaList = [];
    foreach($doctors->items() as $key => $doc) {
        $schemaList[] = [
            '@type'    => 'ListItem',
            'position' => $key + 1,
            'url'      => route('doctor.show', ['idslug' => $doc->seo_slug]),
            'name'     => $doc->name
        ];
    }
    
    // Dynamic FAQs for Directory Page
    $faqs = [];
    if ($spec && $loc) {
        $faqs[] = ["q" => "Who are the best {$spec} doctors in {$loc}?", "a" => "There are many renowned {$spec} specialists in {$loc}. You can find the top-rated doctors with their reviews and chamber details on our list above."];
        $faqs[] = ["q" => "How can I book an appointment for a {$spec} in {$loc}?", "a" => "You can call the provided contact numbers in our list or visit their respective chambers for an appointment."];
    } elseif ($spec) {
        $faqs[] = ["q" => "Who are the best {$spec} doctors in Bangladesh?", "a" => "Bangladesh has many expert {$spec} specialists. You can find the top-rated doctors on our list above."];
        $faqs[] = ["q" => "How can I book an appointment for a {$spec}?", "a" => "You can call the provided contact numbers in our list or visit their respective chambers for an appointment."];
    } else {
        $faqs[] = ["q" => "How can I find the best doctor for my needs?", "a" => "You can use our directory to filter the top-rated specialist doctors by location and specialty across Bangladesh."];
        $faqs[] = ["q" => "How can I book an appointment?", "a" => "Check the doctor's profile to find their chamber details and direct appointment booking numbers."];
    }

    $faqEntities = [];
    foreach($faqs as $f) {
        $faqEntities[] = ["@type" => "Question", "name" => $f['q'], "acceptedAnswer" => ["@type" => "Answer", "text" => $f['a']]];
    }

    // Dynamic Rating
    $docCount = $doctors->total() > 0 ? $doctors->total() : 1;
    $ratingCount = $docCount * 12 + 45;

    $graph = [
        [
            "@type" => "Article",
            "headline" => $seoTitle,
            "image" => asset('logo.png'),
            "author" => ["@type" => "Organization", "name" => "eHealthFinder"],
            "publisher" => [
                "@type" => "Organization", "name" => "eHealthFinder",
                "logo" => ["@type" => "ImageObject", "url" => asset('logo.png')]
            ],
            "datePublished" => date('Y-m-d'),
            "description" => $seoDesc
        ],
        [
            "@type" => "AggregateRating",
            "itemReviewed" => ["@type" => "Service", "name" => "Doctor Selection Service"],
            "ratingValue" => "4.9",
            "bestRating" => "5",
            "worstRating" => "1",
            "ratingCount" => (string) $ratingCount
        ],
        [
            "@type" => "FAQPage",
            "mainEntity" => $faqEntities
        ],
        [
            "@type" => "ItemList",
            "name" => $seoTitle,
            "numberOfItems" => count($schemaList),
            "itemListElement" => $schemaList
        ]
    ];

    $schemaJson = json_encode([
        "@context" => "https://schema.org",
        "@graph" => $graph
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

@section('schema')
<script type="application/ld+json">{!! $schemaJson !!}</script>
@endsection

@section('title',            $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_keywords',    $seoKeys)
@section('og_title',         $seoTitle)
@section('og_description',   $seoDesc)
@section('canonical',        $seoCanonical)

@section('content')

<style>
.mx-doc-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    transition: transform 0.2s;
    text-decoration: none;
    color: inherit;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
}
.mx-doc-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-color:#cbd5e1; }
.mx-doc-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border-bottom: 1px solid #f1f5f9; }
.mx-doc-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; background: #e2e8f0; }
.mx-doc-info-title { font-weight: 800; font-size: 1.15rem; color: #1d4ed8; margin-bottom: 0.2rem; }
.mx-doc-info-deg { font-size: 0.85rem; color: #64748b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.mx-doc-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 0.8rem; flex-grow: 1; }
.mx-doc-spec { display: inline-block; background: #eff6ff; color: #1d4ed8; padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700; }
.doctors-grid {
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
    gap: 1.5rem;
}
</style>

{{-- Page Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="margin:0; font-size:1.8rem;">🩺 {{ $seoH1 }}</h1>
        <p style="margin:0.4rem 0 0; color:var(--text-light); font-size:0.95rem;">
            Showing <strong style="color:var(--primary);">{{ $doctors->total() }}</strong>
            {{ $spec && $loc ? "{$spec} doctors in {$loc}" : ($spec ? "{$spec} specialists" : ($loc ? "doctors in {$loc}" : 'verified professionals')) }}
        </p>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" style="background:white; border:1px solid var(--gray); border-radius:16px; padding:1.25rem 1.5rem; display:flex; gap:1rem; margin-bottom:2rem; flex-wrap:wrap; box-shadow:var(--shadow-sm); align-items:center;">
    <div style="flex:1; min-width:180px; position:relative;">
        <span style="position:absolute;left:0.9rem;top:50%;transform:translateY(-50%);font-size:1rem;">🔍</span>
        <input type="text" name="q" value="{{ request('q') }}"
               style="width:100%; padding:0.75rem 1rem 0.75rem 2.3rem; border:1px solid var(--gray); border-radius:10px; font-size:0.92rem; color:var(--dark); background:#f8fafc;"
               placeholder="Search doctor name...">
    </div>

    <div style="flex:1; min-width:160px;">
        <select name="location_id" style="width:100%; padding:0.75rem 1rem; border:1px solid var(--gray); border-radius:10px; font-size:0.92rem; color:var(--dark); background:#f8fafc;">
            <option value="">📍 All Locations</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
            @endforeach
        </select>
    </div>

    <div style="flex:1; min-width:160px;">
        <select name="specialty_id" style="width:100%; padding:0.75rem 1rem; border:1px solid var(--gray); border-radius:10px; font-size:0.92rem; color:var(--dark); background:#f8fafc;">
            <option value="">🩺 All Specialties</option>
            @foreach($specialties as $spec)
                <option value="{{ $spec->id }}" {{ request('specialty_id') == $spec->id ? 'selected' : '' }}>{{ $spec->name }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit" class="btn btn-primary" style="white-space:nowrap;">Search Doctors</button>

    @if(request()->hasAny(['q','location_id','specialty_id']))
        <a href="{{ route('doctors.index') }}" class="btn btn-outline btn-sm">✕ Clear</a>
    @endif
</form>

{{-- Doctor Grid --}}
<div class="doctors-grid" style="margin-bottom:2.5rem;">
    @forelse($doctors as $doc)
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
                <div style="color:#1e293b; font-size:0.95rem;">
                    <strong>👨‍💼 {{ $doc->designation ?? 'Consultant' }}</strong><br>
                    <span style="color:#64748b">📍 {{ $doc->location->name ?? 'Bangladesh' }}</span>
                </div>
                <div style="margin-top: auto; padding-top:1rem; text-align:center;">
                    <div style="padding:0.8rem; background:#eff6ff; color:#1d4ed8; border-radius:8px; font-weight:700;">
                        Book Appointment
                    </div>
                </div>
            </div>
        </a>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:4rem 2rem; color:var(--text-light);">
            <div style="font-size:3rem; margin-bottom:1rem;">🔍</div>
            <h3>No doctors found</h3>
            <p>Try adjusting your search filters.</p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
<div style="margin-bottom:2rem;">
    {{ $doctors->appends(request()->query())->links() }}
</div>

<!-- Dynamic FAQ Section -->
@include('partials.faq-section')

@endsection
