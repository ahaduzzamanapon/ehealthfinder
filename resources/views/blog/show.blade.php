@extends('layouts.app')

@section('title', $post->seo_title ?? $post->title . ' - eHealthFinder')
@section('meta_description', $post->seo_description)
@section('meta_keywords', $post->tags ?? 'health, care, wellness, tips, bangladesh')

@section('custom_meta')
<!-- Open Graph / Facebook -->
<meta property="og:type" content="article">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $post->seo_title ?? $post->title }}">
<meta property="og:description" content="{{ $post->seo_description }}">
<meta property="og:image" content="{{ $post->featured_image ? url(Storage::url($post->featured_image)) : url('/default-og.png') }}">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ url()->current() }}">
<meta name="twitter:title" content="{{ $post->seo_title ?? $post->title }}">
<meta name="twitter:description" content="{{ $post->seo_description }}">
<meta name="twitter:image" content="{{ $post->featured_image ? url(Storage::url($post->featured_image)) : url('/default-og.png') }}">

{{-- JSON-LD Schema equivalent to Shajgoj article structure --}}
@php
    // Fetch reviews
    $avgRating = $post->averageRating;
    $reviewCount = $post->reviewCount;
    $firstReview = $post->reviews()->where('is_approved', true)->latest()->first();

    // Dynamic FAQ for Blog based on Sections
    $faqs = [];
    foreach($post->sections as $sec) {
        if (!empty($sec->heading) && !empty($sec->content)) {
            $faqs[] = [
                "q" => strip_tags($sec->heading),
                "a" => Str::limit(strip_tags($sec->content), 250)
            ];
        }
    }
    $faqs = array_slice($faqs, 0, 4); // limit to 4

    $docSchema = [
        "@context" => "https://schema.org",
        "@type" => "Article",
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => url()->current()
        ],
        "headline" => $post->seo_title ?? $post->title,
        "description" => $post->seo_description,
        "image" => [
            $post->featured_image ? url(Storage::url($post->featured_image)) : url('/default-og.png')
        ],
        "datePublished" => $post->created_at->toIso8601String(),
        "dateModified" => $post->updated_at->toIso8601String(),
        "author" => [
            "@type" => "Person",
            "name" => $post->author_name ?? 'eHealthFinder'
        ],
        "publisher" => [
            "@type" => "Organization",
            "name" => "eHealthFinder",
            "logo" => [
                "@type" => "ImageObject",
                "url" => url('/logo.png')
            ]
        ]
    ];

    if ($reviewCount > 0) {
        $docSchema["aggregateRating"] = [
            "@type"       => "AggregateRating",
            "ratingValue" => number_format($avgRating, 1),
            "reviewCount" => (string)$reviewCount
        ];
    }
    
    if ($firstReview) {
        $docSchema["review"] = [
            "@type"        => "Review",
            "reviewRating" => [
                "@type"       => "Rating",
                "ratingValue" => (string)$firstReview->rating,
                "bestRating"  => "5"
            ],
            "author"       => [
                "@type" => "Person",
                "name"  => $firstReview->author_name
            ],
            "reviewBody"   => strip_tags($firstReview->body ?? "Great article.")
        ];
    }

    $faqSchema = null;
    if (count($faqs) > 0) {
        $faqEntities = [];
        foreach($faqs as $f) {
            $faqEntities[] = [
                "@type" => "Question",
                "name" => $f['q'],
                "acceptedAnswer" => ["@type" => "Answer", "text" => $f['a']]
            ];
        }
        $faqSchema = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $faqEntities
        ];
    }

    $schemas = [$docSchema];
    if ($faqSchema) $schemas[] = $faqSchema;
@endphp
<script type="application/ld+json">
{!! json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')
<style>
.article-wrapper {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.06);
    overflow: hidden;
}
.article-header { padding: 3rem 3rem 0; }
.article-cat { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: #4f46e5; margin-bottom: 1rem; letter-spacing: 0.05em; display: inline-block; background: rgba(79,70,229,0.1); padding: 0.4rem 1rem; border-radius: 20px; }
.article-title { font-size: 2.5rem; font-weight: 800; color: #0f172a; line-height: 1.3; margin-bottom: 1.5rem; }
.article-meta { display: flex; align-items: center; gap: 1.5rem; color: #64748b; font-size: 0.95rem; margin-bottom: 2rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 2rem; }
.article-featured-img { width: 100%; height: auto; max-height: 500px; object-fit: cover; }
.article-body { padding: 3rem; font-size: 1.15rem; color: #334155; line-height: 1.8; }
.article-body p { margin-bottom: 1.5rem; }
.article-body h2, .article-body h3 { color: #0f172a; font-weight: 700; margin-top: 2.5rem; margin-bottom: 1rem; }
.article-section { margin-bottom: 3rem; }
.article-section-img { width: 100%; border-radius: 12px; margin: 1.5rem 0; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }

@media (max-width: 768px) {
    .article-header, .article-body { padding: 1.5rem; }
    .article-title { font-size: 2rem; }
}
</style>

<div class="row" style="max-width: 1200px; margin: 0 auto;">
    <div class="col-lg-8 mb-4">
        
        <article class="article-wrapper mb-5">
            <header class="article-header">
                <div class="article-cat">{{ $post->category?->name ?? 'Health Tips' }}</div>
                <h1 class="article-title">{{ $post->title }}</h1>
                <div class="article-meta">
                    <span><strong>✍️ {{ $post->author_name ?? 'eHealthFinder' }}</strong></span>
                    <span>📅 {{ $post->created_at->format('F d, Y') }}</span>
                </div>
            </header>

            @if($post->featured_image)
                <img onerror="this.outerHTML='💊'" src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="article-featured-img">
            @endif

            <div class="article-body">
                @if($post->excerpt)
                    <p style="font-size: 1.25rem; font-style: italic; color: #475569; border-left: 4px solid #4f46e5; padding-left: 1.5rem; margin-bottom: 2.5rem;">
                        {{ $post->excerpt }}
                    </p>
                @endif

                {{-- Loop through all dynamically created blocks/sections --}}
                @foreach($post->sections as $sec)
                    <section class="article-section">
                        @if($sec->heading)
                            <h2>{{ $sec->heading }}</h2>
                        @endif

                        @if($sec->content)
                            {!! $sec->content !!}
                        @endif

                        @if($sec->image_path)
                            <img onerror="this.outerHTML='💊'" src="{{ Storage::url($sec->image_path) }}" alt="{{ $sec->heading ?? $post->title }}" class="article-section-img">
                        @endif
                    </section>
                @endforeach
                
                @if($post->tags)
                    <div style="margin-top: 3rem; padding-top: 1.5rem; border-top: 1px dashed #e2e8f0;">
                        <span style="font-weight: 700; color: #475569; margin-right: 0.5rem;">Tags:</span>
                        @foreach(explode(',', $post->tags) as $tag)
                            <a href="{{ route('blog.index', ['q' => trim($tag)]) }}" style="display: inline-block; background: #f1f5f9; color: #334155; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600; text-decoration: none; margin-right: 0.5rem; transition: 0.2s;">#{{ trim($tag) }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
            
            <div style="padding: 0 3rem;">
                <!-- Dynamic FAQ Section -->
                @include('partials.faq-section')
                
                <!-- Polymorphic User Reviews & Ratings -->
                @include('partials.review-section', ['model' => $post])
            </div>

            <div style="background: #f8fafc; padding: 2rem 3rem; border-top: 1px solid #e2e8f0; text-align:center;">
                <h4 style="font-weight: 700; color: #0f172a; margin-bottom: 0.5rem;">Share this article</h4>
                <div style="display:flex; gap:1rem; justify-content:center; margin-top:1rem;">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/' . $post->slug)) }}" target="_blank" style="padding: 0.6rem 1.2rem; background: #1877F2; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url('/' . $post->slug)) }}&text={{ urlencode($post->title) }}" target="_blank" style="padding: 0.6rem 1.2rem; background: #1DA1F2; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Twitter</a>
                </div>
            </div>

            @if(count($faqs) > 0)
                <div style="padding: 0 3rem 2rem;">
                    @include('partials.faq-section', ['faqs' => $faqs])
                </div>
            @endif

        </article>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        @if(isset($relatedPosts) && $relatedPosts->count() > 0)
        <div class="related-sidebar" style="background: white; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: sticky; top: 120px;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 1.5rem; border-bottom: 2px solid #f8fafc; padding-bottom: 1rem;">
                Related Articles
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 1.2rem;">
                @foreach($relatedPosts as $rPost)
                    <a href="{{ url('/' . $rPost->slug) }}" style="display: flex; gap: 1rem; text-decoration: none; color: inherit; align-items: flex-start; transition: transform 0.2s; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                        <div style="width: 90px; height: 75px; flex-shrink: 0; background: #e2e8f0; border-radius: 8px; overflow: hidden; position: relative;">
                            @if($rPost->featured_image)
                                <img onerror="this.outerHTML='💊'" src="{{ Storage::url($rPost->featured_image) }}" alt="{{ $rPost->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div style="width: 100%; height: 100%; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:1.2rem;">📰</div>
                            @endif
                        </div>
                        <div>
                            <h4 style="margin: 0 0 0.4rem 0; font-size: 0.95rem; font-weight: 700; color: #1e293b; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.2s;" onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#1e293b'">
                                {{ $rPost->title }}
                            </h4>
                            <p style="margin: 0; color: #64748b; font-size: 0.8rem;">
                                📅 {{ $rPost->created_at->format('M d, Y') }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
