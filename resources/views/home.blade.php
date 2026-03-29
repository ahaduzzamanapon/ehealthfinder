@extends('layouts.app')

@section('title', 'eHealthFinder | Find Specialist Doctors & Medicines in Bangladesh')
@section('meta_description', 'Find verified specialist doctors in Dhaka, Chittagong, Rangpur, Sylhet and across Bangladesh. Search medicine prices, generic info, and book appointments on eHealthFinder.')
@section('meta_keywords', 'specialist doctor Bangladesh, find doctor online, doctor Dhaka, medicine price Bangladesh, book doctor appointment, ehealthfinder, specialist doctor near me')
@section('og_title', 'eHealthFinder | Find Specialist Doctors & Medicines in Bangladesh')
@section('og_description', 'Search thousands of verified specialist doctors and medicines across Bangladesh. Find chamber details, visiting hours, and appointment numbers.')

@section('content')
<!-- GORGEOUS HERO SECTION -->
<div class="hero">
    <div class="hero-content">
        <h1>Find the Right Care, Right Now</h1>
        <p>Search thousands of verified specialist doctors and comprehensive medicine information across Bangladesh.</p>

        <!-- SMART GLOBAL SEARCH -->
        <div id="smart-search-wrap" style="position:relative; max-width: 700px; margin: 2rem auto 1.5rem;">
            <div style="display:flex; background:white; border-radius: 50px; overflow:visible; box-shadow: 0 8px 40px rgba(0,0,0,0.25); position:relative;">
                <span style="padding: 0 1.2rem; display:flex; align-items:center; font-size:1.3rem;">🔍</span>
                <input 
                    id="smart-search-input"
                    type="text" 
                    autocomplete="off"
                    placeholder='Try "Cancer Surgeon" or "Rangpur" ...'
                    style="flex:1; border:none; outline:none; font-size:1.05rem; padding:1.1rem 0.5rem; font-family:inherit; color:#1e293b; background:transparent;"
                />
                <button 
                    onclick="document.getElementById('smart-search-form').submit()"
                    style="background: linear-gradient(135deg, #4f46e5, #06b6d4); color:white; border:none; border-radius:0 50px 50px 0; padding:0 2rem; font-weight:700; font-size:1rem; cursor:pointer; white-space:nowrap;">
                    Search
                </button>
            </div>
            <!-- Hidden form to redirect on Enter -->
            <form id="smart-search-form" action="{{ route('doctors.index') }}" method="GET" style="display:none">
                <input type="hidden" id="smart-q" name="q">
                <input type="hidden" id="smart-spec" name="specialty_id">
                <input type="hidden" id="smart-loc" name="location_id">
            </form>
            <!-- Autocomplete Dropdown -->
            <div id="smart-dropdown" style="display:none; position:absolute; top:calc(100% + 8px); left:0; right:0; background:white; border-radius:16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); z-index:1000; overflow-y:auto; max-height:380px; border:1px solid #e2e8f0;"></div>
        </div>

       
    </div>
</div>

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
            // fallback: just search by text
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
                <span style="font-size:1.3rem;">${typeIcons[item.type]||'🔍'}</span>
                <div style="flex:1">
                    <div style="font-weight:700; color:#1e293b; font-size:0.95rem;">${highlight(item.label, q)}</div>
                    <div style="font-size:0.8rem; color:#94a3b8;">${item.sub||''}</div>
                </div>
                <span style="font-size:0.75rem; background:${item.type==='combo'?'#eff6ff':'#f0fdf4'}; color:${item.type==='combo'?'#1d4ed8':'#166534'}; padding:2px 8px; border-radius:20px; font-weight:600;">${item.type==='combo'?'Location':'Doctor'}</span>
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

        // Style active state via inline approach
        const style = document.getElementById('ss-style') || (() => {
            const s = document.createElement('style');
            s.id = 'ss-style';
            document.head.appendChild(s);
            return s;
        })();
        style.textContent = '.ss-item.active, .ss-item:hover { background: #f8faff !important; }';
    }

    function highlight(text, q) {
        const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
        return text.replace(re, '<mark style="background:#dbeafe;color:#1d4ed8;border-radius:3px;padding:0 2px">$1</mark>');
    }
})();
</script>



<!-- QUICK STATS -->
<div class="stats-row">
    <div class="stat-card blue">
        <div class="stat-num">{{ number_format($stats['doctors']) }}+</div>
        <div class="stat-label">Specialist Doctors</div>
    </div>
    <div class="stat-card teal">
        <div class="stat-num">{{ number_format($stats['medicines']) }}+</div>
        <div class="stat-label">Medicines & Brands</div>
    </div>
    <div class="stat-card green">
        <div class="stat-num">{{ number_format($stats['locations']) }}</div>
        <div class="stat-label">Divisions & Districts</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-num">{{ number_format($stats['specialties']) }}</div>
        <div class="stat-label">Medical Specialties</div>
    </div>
</div>

<!-- TOP SPECIALTIES -->
<div class="section-head" style="margin-bottom: 1.5rem;">
    <h2>Browse by <span>Specialty</span></h2>
</div>
<div style="display: flex; gap: 0.8rem; flex-wrap: wrap; margin-bottom: 4rem;">
    @foreach($specialties as $spec)
        <a href="{{ route('doctors.index', ['specialty_id' => $spec->id]) }}" 
           style="background: white; border: 1px solid var(--gray); padding: 0.6rem 1.2rem; border-radius: 50px; font-weight: 600; color: var(--dark); font-size: 0.95rem; box-shadow: var(--shadow-sm); transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.3rem;">
           🩺 {{ $spec->name }}
        </a>
    @endforeach
    <a href="{{ route('doctors.index') }}" style="background: rgba(79,70,229,0.1); color: var(--primary); padding: 0.6rem 1.2rem; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: all 0.2s;">
        View All &rarr;
    </a>
</div>

<!-- BROWSE BY CITY -->
<div class="section-head" style="margin-bottom: 1.5rem;">
    <h2>Browse by <span>City</span></h2>
</div>
<div style="display: flex; gap: 0.8rem; flex-wrap: wrap; margin-bottom: 4rem;">
    @foreach($locations as $loc)
        <a href="{{ route('doctors.index', ['location_id' => $loc->id]) }}" 
           style="background: white; border: 1px solid var(--gray); padding: 0.6rem 1.2rem; border-radius: 50px; font-weight: 600; color: var(--dark); font-size: 0.95rem; box-shadow: var(--shadow-sm); transition: all 0.2s; display: inline-flex; align-items: center; gap: 0.3rem;">
           📍 {{ $loc->name }}
        </a>
    @endforeach
</div>

<!-- FEATURED DOCTORS -->
<div class="section-head">
    <h2>Featured <span>Doctors</span></h2>
    <a href="{{ route('doctors.index') }}" class="btn btn-outline btn-sm">View All Doctors</a>
</div>
<div class="grid-3" style="margin-bottom: 4rem;">
    @foreach($featuredDoctors as $doc)
        <a href="{{ route('doctor.show', ['idslug' => $doc->id . '-' . Str::slug($doc->name)]) }}" style="color:inherit">
            <div class="card clearfix">
                @php $safeImg = str_replace('\\', '/', $doc->image_path); @endphp
                <img src="{{ Str::startsWith($safeImg, 'http') ? $safeImg : ($safeImg ? asset($safeImg) : 'https://ui-avatars.com/api/?name='.urlencode($doc->name).'&background=random') }}" class="doc-img" alt="{{ $doc->name }}">
                <h3 class="card-title" style="margin-bottom: 0.1rem;">{{ $doc->name }}</h3>
                <div class="card-subtitle" style="margin-bottom: 0.8rem;">{{ $doc->degrees }}</div>
                <div class="tags">
                    @if($doc->specialty)<span class="tag specialty">{{ $doc->specialty->name }}</span>@endif
                    @if($doc->location)<span class="tag location">{{ $doc->location->name }}</span>@endif
                </div>
            </div>
        </a>
    @endforeach
</div>

<!-- POPULAR MEDICINES -->
<div class="section-head" style="margin-top: 2rem;">
    <h2>Popular <span>Medicines</span></h2>
    <a href="{{ route('medicines.index') }}" class="btn btn-outline btn-sm">Explore Index</a>
</div>
<div class="grid-3" style="margin-bottom: 4rem;">
    @foreach($featuredBrands as $brand)
        <a href="{{ route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug]) }}" style="color:inherit; text-decoration:none;">
            <div class="card" style="padding: 1.5rem; transition: transform 0.2s; border-left: 4px solid var(--primary)">
                <h3 style="margin-top:0; margin-bottom: 0.3rem; color:var(--primary-dark)">{{ $brand->name }} <span style="background:var(--bg); color:var(--text-light); padding:2px 8px; border-radius:12px; font-size:0.8rem; margin-left: 6px; border: 1px solid var(--gray);">{{ $brand->dosage_form }}</span></h3>
                <p style="margin-bottom:0.8rem; font-size:0.9rem; color: var(--text);"><strong>{{ $brand->company }}</strong></p>
                <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                    <p style="margin:0; font-weight:800; color:#10b981; font-size: 1.2rem;">{{ $brand->price }}</p>
                    <span style="font-size: 0.8rem; color: #1e40af; font-weight: 600;">{{ $brand->generic->name ?? 'View Details' }}</span>
                </div>
            </div>
        </a>
    @endforeach
</div>
<!-- SEO QUICK LINKS -->
<div class="section-head" style="margin-top: 5rem; margin-bottom: 1.5rem;">
    <h2>Popular <span>Doctor Searches</span></h2>
</div>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    @php
        // Take a few top locations and specialties to generate high-value SEO links
        $topLocs = $locations->take(4);
        $topSpecs = $specialties->take(6);
    @endphp
    @foreach($topSpecs as $s)
        @foreach($topLocs as $l)
            <a href="{{ route('doctors.index', ['specialty_id' => $s->id, 'location_id' => $l->id]) }}" 
               style="background: white; border: 1px solid var(--gray); padding: 0.8rem 1rem; border-radius: 12px; font-size: 0.9rem; font-weight: 600; color: var(--text); transition: all 0.2s; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 0.5rem;">
               <span style="color: var(--primary);">📍</span> {{ $s->name }} in {{ $l->name }}
            </a>
        @endforeach
    @endforeach
</div>

@endsection
