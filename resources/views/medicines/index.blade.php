@extends('layouts.app')

@section('title', 'Medicine Directory | eHealthFinder')
@section('meta_description', 'Search the comprehensive medicine index of Bangladesh. Find drug prices, generic names, companies, and antibiotic status.')

@section('content')

<style>
.mdx-page-title { font-size: 2.5rem; font-weight: 800; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
.mdx-page-subtitle { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 3rem; }
.mdx-search-box {
    background: white; padding: 0.8rem 1rem 0.8rem 1.5rem; border-radius: 50px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06); display: flex; align-items: center;
    max-width: 800px; margin: 0 auto 3.5rem; border: 1px solid #e2e8f0;
}
.mdx-search-input { flex: 1; border: none; outline: none; padding: 0.5rem 1rem; font-size: 1.15rem; color: #334155; background: transparent; }
.mdx-search-btn {
    background: #2563eb; color: white; border: none; padding: 1rem 2.5rem;
    border-radius: 50px; font-weight: 700; font-size: 1.05rem; cursor: pointer; transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}
.mdx-search-btn:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.3); }

/* Medicine Card Grid */
.med-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
}
.med-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    text-decoration: none;
    position: relative;
}
.med-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.08);
    border-color: #cbd5e1;
}
.med-card-img-wrap {
    height: 180px;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}
.med-card-img-wrap img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    mix-blend-mode: multiply;
}
.med-placeholder-icon {
    font-size: 4rem;
    color: #cbd5e1;
}
.med-card-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.med-badge-antibiotic {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #fef2f2;
    color: #ef4444;
    border: 1px solid #fecaca;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 800;
    box-shadow: 0 2px 5px rgba(239, 68, 68, 0.2);
}
.med-card-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1e3a8a;
    margin: 0 0 0.5rem 0;
    line-height: 1.3;
}
.med-card-form {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.med-card-generic {
    font-size: 0.9rem;
    color: #475569;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}
.med-card-company {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}
.med-card-footer {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px dashed #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}
.med-card-price {
    font-size: 0.95rem;
    font-weight: 700;
    color: #10b981;
    line-height: 1.5;
}
.med-card-view-btn {
    display: block;
    width: 100%;
    text-align: center;
    background: #eff6ff;
    color: #2563eb;
    padding: 0.6rem 0;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.2s;
}
.med-card:hover .med-card-view-btn {
    background: #2563eb;
    color: white;
}
</style>

<div class="breadcrumb" style="margin-bottom: 2rem;">
    <a href="{{ route('home') }}">Home</a> ›
    <span style="color: #64748b;">Medicines</span>
</div>

<h1 class="mdx-page-title">Medicine Database</h1>
<p class="mdx-page-subtitle">Search comprehensive medicine information across Bangladesh</p>

<form method="GET" class="mdx-search-box">
    <span style="font-size: 1.3rem; margin-left:1rem; color:#94a3b8;">🔍</span>
    <input type="text" name="q" value="{{ request('q') }}" class="mdx-search-input" placeholder="Search brand or generic name...">
    <button type="submit" class="mdx-search-btn">Search &rarr;</button>
</form>

<div class="med-grid">
    @forelse($brands as $brand)
        <a href="{{ route('medicine.show', ['id' => $brand->id, 'slug' => Str::slug($brand->name)]) }}" class="med-card">
            @if($brand->is_antibiotic)
                <div class="med-badge-antibiotic">⚠️ Antibiotic</div>
            @endif
            
            <div class="med-card-img-wrap">
                @if($brand->image_path)
                    @php $safeImg = str_replace('\\', '/', $brand->image_path); @endphp
                    <img loading="lazy" src="{{ Str::startsWith($safeImg,'http') ? $safeImg : asset($safeImg) }}" alt="{{ $brand->name }}">
                @else
                    <div class="med-placeholder-icon">💊</div>
                @endif
            </div>
            
            <div class="med-card-body">
                <div>
                    <h3 class="med-card-title">{{ $brand->name }}</h3>
                    <span class="med-card-form">{{ $brand->dosage_form }}</span>
                </div>
                
                <div class="med-card-generic">
                    <span style="flex-shrink: 0;">🧬</span>
                    <span>{{ $brand->generic ? $brand->generic->name : 'N/A' }}</span>
                </div>
                
                <div class="med-card-company">
                    <span style="flex-shrink: 0;">🏢</span>
                    <span>{{ $brand->company ?: 'N/A' }}</span>
                </div>
                
                <div class="med-card-footer">
                    <div class="med-card-price">{{ $brand->price ?: 'Price N/A' }}</div>
                    <div class="med-card-view-btn">View Details &rarr;</div>
                </div>
            </div>
        </a>
    @empty
        <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; background: white; border-radius: 16px; border: 1px dashed #cbd5e1;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
            <h3 style="font-size: 1.5rem; color: #0f172a; margin-bottom: 0.5rem; font-weight: 800;">No medicines found</h3>
            <p style="color: #64748b; font-size: 1.1rem;">Try searching for a different brand or generic name.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 3rem; display: flex; justify-content: center;">
    {{ $brands->appends(request()->query())->links() }}
</div>

@php
    $searchQuery = request('q');
    $faqs = [];
    if($searchQuery) {
        $faqs[] = ["q" => "What medicines are related to '{$searchQuery}' in Bangladesh?", "a" => "eHealthFinder currently lists {$brands->total()} medicines matching '{$searchQuery}'. You can view their indications, side effects, and exact unit prices."];
        $faqs[] = ["q" => "Are the prices shown for '{$searchQuery}' accurate?", "a" => "Yes, the prices shown for '{$searchQuery}' and related medications are updated from pharmaceutical brands in Bangladesh."];
    } else {
        $faqs[] = ["q" => "How many medicines are listed on eHealthFinder?", "a" => "eHealthFinder provides an extensive database of thousands of medicines, complete with genuine unit prices, generics, and pharmaceutical companies."];
        $faqs[] = ["q" => "How can I find the generic alternative of a medicine?", "a" => "Each medicine card lists its active generic ingredient. Searching by this generic name will return all alternate brands."];
    }

    $faqEntities = [];
    foreach($faqs as $f) {
        $faqEntities[] = [
            "@type" => "Question",
            "name" => $f['q'],
            "acceptedAnswer" => ["@type" => "Answer", "text" => $f['a']]
        ];
    }

    $itemListElements = [];
    $pos = 1;
    foreach($brands as $b) {
        $itemListElements[] = [
            "@type" => "ListItem",
            "position" => $pos++,
            "url" => route('medicine.show', ['id' => $b->id, 'slug' => \Illuminate\Support\Str::slug($b->name)]),
            "name" => $b->name
        ];
    }

    $indexSchemaJson = json_encode([
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "FAQPage",
                "mainEntity" => $faqEntities
            ],
            [
                "@type" => "ItemList",
                "itemListElement" => $itemListElements
            ]
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

@section('schema')
<script type="application/ld+json">{!! $indexSchemaJson !!}</script>
@endsection

@include('partials.faq-section', ['faqs' => $faqs])

@endsection
