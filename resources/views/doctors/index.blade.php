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
@endphp

@section('title',            $seoTitle)
@section('meta_description', $seoDesc)
@section('meta_keywords',    $seoKeys)
@section('og_title',         $seoTitle)
@section('og_description',   $seoDesc)
@section('canonical',        $seoCanonical)

@section('content')

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

{{-- Doctor Grid -- using premium doctor-card class --}}
<div class="doctors-grid" style="margin-bottom:2.5rem;">
    @forelse($doctors as $doc)
        @php $safeImg = str_replace('\\', '/', $doc->image_path); @endphp
        <a href="{{ route('doctor.show', ['idslug' => $doc->seo_slug]) }}"
           style="color:inherit; text-decoration:none;"
           title="{{ $doc->name }} – {{ $doc->specialty?->name ?? '' }} in {{ $doc->location?->name ?? 'Bangladesh' }}">
            <div class="doctor-card">
                <div class="doctor-card-image">
                    <img src="{{ $safeImg && Str::startsWith($safeImg,'http') ? $safeImg : ($safeImg ? asset($safeImg) : 'https://ui-avatars.com/api/?name='.urlencode($doc->name).'&background=4f46e5&color=fff&size=300') }}"
                         alt="{{ $doc->name }} - {{ $doc->specialty?->name ?? 'Doctor' }} in {{ $doc->location?->name ?? 'Bangladesh' }}"
                         loading="lazy">
                    <div class="img-overlay"></div>
                </div>
                <div class="doctor-card-body">
                    <h3 class="doctor-card-name">{{ $doc->name }}</h3>
                    <p class="doctor-card-deg">{{ Str::limit($doc->degrees, 70) }}</p>
                    <div class="tags" style="margin-top:0.75rem; justify-content:flex-start;">
                        @if($doc->specialty)<span class="tag specialty">{{ $doc->specialty->name }}</span>@endif
                        @if($doc->location)<span class="tag location">📍 {{ $doc->location->name }}</span>@endif
                    </div>
                    @if($doc->designation)
                    <p style="font-size:0.8rem; color:var(--text-light); margin-top:0.75rem; line-height:1.4;">
                        {{ Str::limit($doc->designation, 65) }}
                    </p>
                    @endif
                    <div style="margin-top:1rem; padding-top:0.75rem; border-top:1px solid var(--gray); display:flex; justify-content:flex-end;">
                        <span style="font-size:0.82rem; color:var(--primary); font-weight:600;">View Profile →</span>
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

@endsection
