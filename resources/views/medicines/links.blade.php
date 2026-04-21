@extends('layouts.app')

@section('title', "All Medicines A-Z — Letter {$letter} | eHealthFinder")
@section('meta_description', "Browse all medicines starting with letter {$letter} on eHealthFinder. Complete drug index with prices, generics and manufacturers in Bangladesh.")
@section('canonical', route('medicine.links', ['letter' => $letter]))

@section('schema')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebPage",
      "@id": "{{ route('medicine.links', ['letter' => $letter]) }}",
      "url": "{{ route('medicine.links', ['letter' => $letter]) }}",
      "name": "All Medicines A-Z — Letter {{ $letter }} | eHealthFinder",
      "description": "Complete medicine index for letter {{ $letter }} on eHealthFinder Bangladesh.",
      "publisher": {
        "@type": "Organization",
        "name": "eHealthFinder",
        "logo": { "@type": "ImageObject", "url": "{{ url('/logo.png') }}" }
      },
      "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
          { "@type": "ListItem", "position": 1, "name": "Home",      "item": "{{ url('/') }}" },
          { "@type": "ListItem", "position": 2, "name": "Medicines", "item": "{{ route('medicines.index') }}" },
          { "@type": "ListItem", "position": 3, "name": "Medicine Index A-Z", "item": "{{ route('medicine.links') }}" }
        ]
      }
    }
  ]
}
</script>
@endsection

@section('content')
<style>
.links-hero {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    padding: 3rem 1.5rem 2rem;
    text-align: center;
}
.links-hero h1 { font-size: 2.2rem; font-weight: 900; margin: 0 0 .5rem; }
.links-hero p  { opacity: .85; font-size: 1rem; margin: 0; }
.links-stats   { display: inline-flex; gap: 2rem; margin-top: 1.25rem; flex-wrap: wrap; justify-content: center; }
.stat-pill {
    background: rgba(255,255,255,.15); border-radius: 50px;
    padding: .4rem 1.1rem; font-size: .9rem; font-weight: 700;
}

/* Alphabet Nav */
.alpha-nav {
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    padding: .85rem 1.5rem;
    display: flex; flex-wrap: wrap; gap: .4rem;
    justify-content: center;
    position: sticky; top: 64px; z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.alpha-btn {
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 8px; font-weight: 800; font-size: .9rem;
    text-decoration: none; transition: all .15s;
    color: #475569; background: #f8fafc; border: 1.5px solid #e2e8f0;
}
.alpha-btn:hover  { background: #eff6ff; border-color: #4f46e5; color: #4f46e5; }
.alpha-btn.active { background: #4f46e5; border-color: #4f46e5; color: #fff; box-shadow: 0 3px 10px rgba(79,70,229,.35); }
.alpha-btn.empty  { opacity: .35; cursor: default; pointer-events: none; }

/* Content */
.links-body {
    max-width: 1200px;
    margin: 2.5rem auto;
    padding: 0 1.5rem 4rem;
}
.section-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem; flex-wrap: wrap; gap: .75rem;
}
.section-header h2 {
    font-size: 1.6rem; font-weight: 900; color: #1e1b4b;
    display: flex; align-items: center; gap: .5rem; margin: 0;
}
.letter-badge {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff; width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; font-weight: 900;
}
.count-chip {
    background: #eff6ff; color: #4f46e5; border: 1px solid #c7d2fe;
    padding: .35rem .9rem; border-radius: 50px; font-size: .85rem; font-weight: 700;
}

/* Grid */
.med-links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: .65rem;
}
.med-link-card {
    background: #fff;
    border: 1px solid #e8edf4;
    border-radius: 12px;
    padding: .85rem 1rem;
    text-decoration: none;
    display: flex; flex-direction: column; gap: .25rem;
    transition: all .18s;
    box-shadow: 0 1px 4px rgba(0,0,0,.03);
}
.med-link-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 16px rgba(79,70,229,.12);
    transform: translateY(-2px);
    background: #fafbff;
}
.med-link-name {
    font-weight: 700; font-size: .95rem; color: #1e293b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.med-link-sub {
    font-size: .78rem; color: #94a3b8;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.med-link-price {
    font-size: .82rem; font-weight: 800; color: #10b981; margin-top: .1rem;
}

/* Pagination */
.pagi-wrap { margin-top: 2rem; display: flex; justify-content: center; }
.pagi-wrap .pagination { display: flex; gap: .4rem; flex-wrap: wrap; list-style: none; padding: 0; margin: 0; }
.pagi-wrap .page-item .page-link {
    padding: .55rem 1rem; border-radius: 10px; border: 1.5px solid #e2e8f0;
    font-weight: 700; color: #475569; text-decoration: none; background: #fff;
    transition: all .15s; font-size: .9rem;
}
.pagi-wrap .page-item.active .page-link { background: #4f46e5; color: #fff; border-color: #4f46e5; }
.pagi-wrap .page-item .page-link:hover  { border-color: #4f46e5; color: #4f46e5; }
</style>

{{-- Hero --}}
<div class="links-hero">
    <h1>💊 Medicine A–Z Index</h1>
    <p>Complete drug directory for Bangladesh — find any medicine instantly</p>
    <div class="links-stats">
        <span class="stat-pill">📋 {{ number_format($totalCount) }} Medicines</span>
        <span class="stat-pill">🔤 Browsing: Letter <strong>{{ $letter }}</strong></span>
        <span class="stat-pill">📄 Page {{ $medicines->currentPage() }} of {{ $medicines->lastPage() }}</span>
    </div>
</div>

{{-- Sticky Alphabet Nav --}}
<nav class="alpha-nav" aria-label="Browse medicines by letter">
    @php $allLetters = range('A','Z'); @endphp
    @foreach($allLetters as $l)
        @php $hasLetter = $letters->contains($l); @endphp
        <a href="{{ $hasLetter ? route('medicine.links', ['letter' => $l]) : '#' }}"
           class="alpha-btn {{ $l === $letter ? 'active' : '' }} {{ !$hasLetter ? 'empty' : '' }}"
           title="{{ $hasLetter ? "Medicines starting with $l" : 'No medicines' }}">
            {{ $l }}
        </a>
    @endforeach
</nav>

{{-- Body --}}
<div class="links-body">
    <div class="section-header">
        <h2>
            <div class="letter-badge">{{ $letter }}</div>
            Medicines — {{ $letter }}
        </h2>
        <span class="count-chip">{{ number_format($medicines->total()) }} results</span>
    </div>

    @if($medicines->isEmpty())
        <div style="text-align:center;padding:4rem 1rem;color:#94a3b8;">
            <div style="font-size:3rem;margin-bottom:1rem;">🔍</div>
            <p style="font-size:1.1rem;font-weight:600;">No medicines found for letter <strong>{{ $letter }}</strong></p>
        </div>
    @else
        <div class="med-links-grid">
            @foreach($medicines as $med)
                <a href="{{ route('medicine.show', ['id' => $med->id, 'slug' => $med->slug ?? \Illuminate\Support\Str::slug($med->name)]) }}"
                   class="med-link-card"
                   title="{{ $med->name }} — {{ $med->dosage_form }} by {{ $med->company }}">
                    <div class="med-link-name">{{ $med->name }}</div>
                    <div class="med-link-sub">
                        {{ $med->dosage_form }}{{ $med->strength ? ' · ' . $med->strength : '' }}
                    </div>
                    @if($med->company)
                        <div class="med-link-sub">{{ $med->company }}</div>
                    @endif
                    @if($med->price)
                        <div class="med-link-price">{{ $med->price }}</div>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($medicines->hasPages())
            <div class="pagi-wrap">
                {{ $medicines->links('pagination::bootstrap-4') }}
            </div>
        @endif
    @endif

    {{-- Letter Quick Jump --}}
    <div style="margin-top:3rem;padding:1.5rem;background:#f8fafc;border-radius:16px;border:1px solid #e2e8f0;">
        <p style="font-weight:800;color:#1e1b4b;margin:0 0 1rem;font-size:1rem;">🔤 Jump to Another Letter</p>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
            @foreach($letters as $l)
                <a href="{{ route('medicine.links', ['letter' => $l]) }}"
                   style="padding:.45rem .9rem;border-radius:8px;border:1.5px solid {{ $l === $letter ? '#4f46e5' : '#e2e8f0' }};
                          background:{{ $l === $letter ? '#4f46e5' : '#fff' }};
                          color:{{ $l === $letter ? '#fff' : '#475569' }};
                          font-weight:700;font-size:.88rem;text-decoration:none;transition:all .15s;"
                   onmouseover="if('{{ $l }}' !== '{{ $letter }}') { this.style.borderColor='#4f46e5'; this.style.color='#4f46e5'; }"
                   onmouseout="if('{{ $l }}' !== '{{ $letter }}') { this.style.borderColor='#e2e8f0'; this.style.color='#475569'; }">
                    {{ $l }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
