@extends('layouts.app')
@section('title', 'Health Blog - eHealthFinder')

@php
    $catSearch = request('category');
    $catName = "Health";
    if($catSearch) {
        $foundCat = isset($categories) ? collect($categories)->where('slug', $catSearch)->first() : null;
        if($foundCat) $catName = $foundCat->name;
    }

    $faqs = [];
    if($catSearch) {
        $faqs[] = ["q" => "Where can I find the best articles about {$catName}?", "a" => "You are browsing the dedicated {$catName} category on eHealthFinder, featuring expert medical insights and lifestyle tips."];
        $faqs[] = ["q" => "Who writes the {$catName} articles?", "a" => "Our {$catName} articles are written and reviewed by verified medical professionals and seasoned health writers."];
    } else {
        $faqs[] = ["q" => "What kind of topics are covered in the eHealthFinder Blog?", "a" => "Our blog covers everything from fitness guidance and nutrition tips to deep-dives into complex medical conditions and pharmaceutical data."];
        $faqs[] = ["q" => "Can I suggest a topic for the blog?", "a" => "Yes, we welcome feedback from patients and doctors alike to cover the most pressing healthcare topics relevant to Bangladesh."];
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
    foreach($posts as $p) {
        $itemListElements[] = [
            "@type" => "ListItem",
            "position" => $pos++,
            "url" => url('/' . $p->slug),
            "name" => $p->title
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

@section('content')
<style>
.blog-header {
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    color: white;
    padding: 3rem 1rem;
    text-align: center;
    border-radius: 16px;
    margin-bottom: 3rem;
    box-shadow: 0 10px 30px rgba(79,70,229,0.3);
}
.blog-header h1 { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; }
.blog-header p { font-size: 1.1rem; opacity: 0.9; }

.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}
.blog-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}
.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}
.blog-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    background: #f1f5f9;
}
.blog-card-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.blog-cat {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: #4f46e5;
    margin-bottom: 0.5rem;
    letter-spacing: 0.05em;
}
.blog-title {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 0.75rem;
    color: #0f172a;
}
.blog-excerpt {
    font-size: 0.95rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex: 1;
}
.blog-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #94a3b8;
    border-top: 1px solid #f1f5f9;
    padding-top: 1rem;
}

.blog-filters {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 2rem;
    justify-content: center;
}
.filter-btn {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    background: #f1f5f9;
    color: #475569;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    transition: 0.2s;
}
.filter-btn:hover { background: #e2e8f0; }
.filter-btn.active { background: #4f46e5; color: white; }
</style>

<div class="row">
    <div class="col-md-10 offset-md-1">
        
        <div class="blog-header">
            <h1>Health & Wellness Blog</h1>
            <p>Expert tips, guides, and medical insights to keep you healthy.</p>
        </div>

        <div class="blog-filters">
            <a href="{{ route('blog.index') }}" class="filter-btn {{ !$categorySlug ? 'active' : '' }}">All Posts</a>
            @foreach($categories as $cat)
                @if($cat->posts_count > 0)
                <a href="{{ route('blog.index', ['category' => $cat->slug]) }}" class="filter-btn {{ $categorySlug == $cat->slug ? 'active' : '' }}">
                    {{ $cat->name }} ({{ $cat->posts_count }})
                </a>
                @endif
            @endforeach
        </div>

        <div class="blog-grid">
            @forelse($posts as $post)
            <a href="{{ url('/' . $post->slug) }}" class="blog-card">
                @if($post->featured_image)
                    <img src="{{ Storage::url($post->featured_image) }}" class="blog-card-img" alt="{{ $post->title }}">
                @else
                    <div class="blog-card-img" style="display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:3rem;">🏥</div>
                @endif
                <div class="blog-card-body">
                    <div class="blog-cat">{{ $post->category?->name ?? 'Uncategorized' }}</div>
                    <h2 class="blog-title">{{ $post->title }}</h2>
                    <p class="blog-excerpt">{{ Str::limit($post->excerpt ?? strip_tags($post->sections->first()?->content), 120) }}</p>
                    <div class="blog-meta">
                        <span>✍️ {{ $post->author_name ?? 'Admin' }}</span>
                        <span>📅 {{ $post->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </a>
            @empty
            <div style="grid-column: 1 / -1; text-align:center; padding: 4rem 1rem; color:#64748b;">
                <h3 style="font-size:1.5rem; margin-bottom:1rem;">No posts found</h3>
                <p>Check back later for exciting new health articles.</p>
            </div>
            @endforelse
        </div>

        <div style="margin-top: 3rem; display:flex; justify-content:center;">
            {{ $posts->links() }}
        </div>

        @include('partials.faq-section', ['faqs' => $faqs])

    </div>
</div>
@endsection
