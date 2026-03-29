@extends('layouts.app')

@section('title', 'Find Specialist Doctors in Bangladesh | eHealthFinder')
@section('meta_description', 'Browse ' . $doctors->total() . ' verified specialist doctors across Bangladesh. Filter by city and specialty to book your appointment.')
@section('meta_keywords', 'specialist doctor Bangladesh, find doctor online, doctor appointment Bangladesh, best doctor dhaka, ehealthfinder')
@section('og_title', 'Find Specialist Doctors in Bangladesh | eHealthFinder')
@section('og_description', 'Search verified specialist doctors across Bangladesh by location and specialty.')

@section('content')

{{-- Page Header --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="margin:0; font-size:1.8rem;">🩺 Doctor Directory</h1>
        <p style="margin:0.4rem 0 0; color:var(--text-light); font-size:0.95rem;">
            Showing <strong style="color:var(--primary);">{{ $doctors->total() }}</strong> verified professionals
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
        <a href="{{ route('doctor.show', ['idslug' => $doc->id . '-' . Str::slug($doc->name)]) }}"
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
