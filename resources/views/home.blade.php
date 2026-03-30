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
<!-- GORGEOUS HERO SECTION -->
<div class="hero">
    <div class="hero-content">
        <h1>Find the Right Care, Right Now</h1>
        <p>Search thousands of verified specialist doctors and comprehensive medicine information across Bangladesh.</p>

        <!-- SMART GLOBAL SEARCH -->
        <div id="smart-search-wrap">
            <div class="smart-search-bar">
                <span class="smart-search-icon">🔍</span>
                <input
                    id="smart-search-input"
                    class="smart-search-input"
                    type="text"
                    autocomplete="off"
                    placeholder='Try "Cancer Surgeon" or "Rangpur" ...'
                />
                <button
                    class="smart-search-btn"
                    onclick="document.getElementById('smart-search-form').submit()">
                    Search
                </button>
            </div>
            <form id="smart-search-form" action="{{ route('doctors.index') }}" method="GET" style="display:none">
                <input type="hidden" id="smart-q" name="q">
                <input type="hidden" id="smart-spec" name="specialty_id">
                <input type="hidden" id="smart-loc" name="location_id">
            </form>
            <div id="smart-dropdown" style="display:none; position:absolute; top:calc(100% + 8px); left:0; right:0; background:white; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.2); z-index:1000; overflow-y:auto; max-height:380px; border:1px solid #e2e8f0;"></div>
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

@php
    /* Build all specialty × location combos with doctor count > 0 */
    $qlSpecs = $specialties;   /* all specialties */
    $qlLocs  = $locations;     /* all locations  */
@endphp

<!-- ═══ POPULAR SEARCHES QUICK LINKS (lazy loaded) ═══ -->
<div class="section-head" style="margin-bottom:1.5rem; margin-top:1rem;">
    <h2>Popular <span>Doctor Searches</span></h2>
    <p style="color:var(--text-light); font-size:0.95rem; margin-top:0.5rem;">Find the right specialist in your city — click any link below.</p>
</div>
<div id="ql-wrapper" style="margin-bottom:3.5rem; min-height:80px;">
    {{-- Skeleton loader --}}
    <div id="ql-skeleton" style="display:flex; flex-wrap:wrap; gap:0.6rem;">
        @for($i=0;$i<12;$i++)
            <span style="height:32px; width:{{ 100+($i%5)*30 }}px; background:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size:400px 100%; animation:shimmer 1.4s infinite; border-radius:50px; display:inline-block;"></span>
        @endfor
    </div>
    <div id="ql-content" style="display:none; flex-wrap:wrap; gap:0.6rem;"></div>
</div>
<style>
@keyframes shimmer { 0%{background-position:-400px 0} 100%{background-position:400px 0} }
</style>
<script>
(function(){
    // Load quick links after page is idle / fully painted
    const load = () => {
        fetch('/api/quick-links')
            .then(r => r.json())
            .then(links => {
                const wrap = document.getElementById('ql-content');
                document.getElementById('ql-skeleton').style.display = 'none';
                wrap.style.display = 'flex';
                wrap.innerHTML = links.map(l =>
                    `<a href="${l.url}" class="ql-pill">
                        <span class="ql-icon">🔍</span>
                        <span class="ql-text">${l.label}</span>
                        <span class="ql-count">${l.count}</span>
                    </a>`
                ).join('') +
                `<a href="/doctors" class="ql-pill ql-pill-more">Browse All Doctors &rarr;</a>`;
            })
            .catch(() => document.getElementById('ql-skeleton').style.display = 'none');
    };
    // Use requestIdleCallback if available, else setTimeout
    if ('requestIdleCallback' in window) {
        requestIdleCallback(load, { timeout: 3000 });
    } else {
        setTimeout(load, 300);
    }
})();
</script>

<div class="section-head" style="margin-bottom: 1.5rem;">
    <h2>Browse by <span>Specialty</span></h2>
</div>
<div style="display: flex; gap: 0.8rem; flex-wrap: wrap; margin-bottom: 4rem;">
    @foreach($specialties as $spec)
        <a href="{{ \App\Helpers\SeoHelper::getSeoUrl($spec->id, null) }}" 
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
        <a href="{{ \App\Helpers\SeoHelper::getSeoUrl(null, $loc->id) }}" 
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
        <a href="{{ route('doctor.show', ['idslug' => $doc->seo_slug]) }}" style="color:inherit">
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
            <a href="{{ \App\Helpers\SeoHelper::getSeoUrl($s->id, $l->id) }}" 
               style="background: white; border: 1px solid var(--gray); padding: 0.8rem 1rem; border-radius: 12px; font-size: 0.9rem; font-weight: 600; color: var(--text); transition: all 0.2s; box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 0.5rem;">
               <span style="color: var(--primary);">📍</span> {{ $s->name }} in {{ $l->name }}
            </a>
        @endforeach
    @endforeach
</div>

@endsection
