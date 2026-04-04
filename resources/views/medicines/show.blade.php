@extends('layouts.app')

@php
    $generic  = $brand->generic?->name ?? '';
    $medUrl   = route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug]);
    $safeImg  = str_replace('\\', '/', $brand->image_path ?? '');
    $imgUrl   = $safeImg ? (Str::startsWith($safeImg, 'http') ? $safeImg : asset($safeImg)) : null;
    $ogImg    = $imgUrl ?? asset('logo.png');
    $titleStr = trim("{$brand->name} {$brand->dosage_form}") . " - Price, Uses and Side Effects";
    $descStr  = "{$brand->name} {$brand->dosage_form} by {$brand->company}. Generic: {$generic}. Price: {$brand->price}. Find indications, dosage, side effects and alternatives on eHealthFinder.";

    // Fetch reviews
    $avgRating = $brand->averageRating;
    $reviewCount = $brand->reviewCount;
    $firstReview = $brand->reviews()->where('is_approved', true)->latest()->first();

    // 1. Drug Schema
    $drugSchema = [
        "@context"           => "https://schema.org",
        "@type"              => "Drug",
        "name"               => $brand->name,
        "image"              => $imgUrl ?? "",
        "brand"              => [
            "@type" => "Brand",
            "name"  => $brand->company ?: "Unknown"
        ],
        "nonProprietaryName" => $generic ?: "Unknown",
        "drugUnit"           => $brand->dosage_form ?: "Tablet",
        "dosageForm"         => $brand->dosage_form ?: "Tablet",
        "description"        => $descStr,
        "indication"         => [
            "@type" => "MedicalIndication",
            "name"  => Str::limit(strip_tags($brand->indications_en ?? $brand->generic?->indications_en ?? "Treatment of applicable conditions"), 100)
        ],
        "prescribingInfo"    => $medUrl,
    ];

    if ($reviewCount > 0) {
        $drugSchema["aggregateRating"] = [
            "@type"       => "AggregateRating",
            "ratingValue" => number_format($avgRating, 1),
            "reviewCount" => (string)$reviewCount
        ];
    }
    
    if ($firstReview) {
        $drugSchema["review"] = [
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
            "reviewBody"   => strip_tags($firstReview->body ?? "Great medicine.")
        ];
    }

    if ($brand->price) {
        preg_match('/(\d+(\.\d+)?)/', str_replace(',', '', $brand->price), $matches);
        $numericPrice = isset($matches[1]) ? (float) $matches[1] : 0.00;
        $drugSchema["offers"] = [
            "@type" => "Offer",
            "price" => $numericPrice,
            "priceCurrency" => "BDT",
            "availability" => "https://schema.org/InStock",
            "url" => $medUrl,
            "hasMerchantReturnPolicy" => [
                "@type" => "MerchantReturnPolicy",
                "applicableCountry" => "BD",
                "returnPolicyCategory" => "https://schema.org/MerchantReturnFiniteReturnPeriod",
                "merchantReturnDays" => 7
            ],
            "shippingDetails" => [
                "@type" => "OfferShippingDetails",
                "shippingRate" => ["@type" => "MonetaryAmount", "value" => 60.00, "currency" => "BDT"],
                "deliveryTime" => [
                    "@type" => "ShippingDeliveryTime",
                    "handlingTime" => ["@type" => "QuantitativeValue", "minValue" => 0, "maxValue" => 1, "unitCode" => "DAY"],
                    "transitTime" => ["@type" => "QuantitativeValue", "minValue" => 1, "maxValue" => 3, "unitCode" => "DAY"]
                ],
                "shippingDestination" => ["@type" => "DefinedRegion", "addressCountry" => "BD"]
            ]
        ];
    }

    // 2. FAQ Schema using Real Database Text limits
    $faqs = [];
    $text_indications = strip_tags($brand->indications_en ?? $brand->generic?->indications_en ?? '');
    if ($text_indications) {
        $faqs[] = ["q" => "What is {$brand->name} used for?", "a" => Str::limit($text_indications, 250)];
    } else {
        $faqs[] = ["q" => "What is {$brand->name} used for?", "a" => "{$brand->name} is primarily used for various conditions indicated by your physician."];
    }

    $text_dosage = strip_tags($brand->dosage_en ?? $brand->generic?->dosage_en ?? '');
    if ($text_dosage) {
        $faqs[] = ["q" => "What is the recommended dosage for {$brand->name}?", "a" => Str::limit($text_dosage, 250)];
    }

    $text_side_effects = strip_tags($brand->side_effects_en ?? $brand->generic?->side_effects_en ?? '');
    if ($text_side_effects) {
        $faqs[] = ["q" => "What are the side effects of {$brand->name}?", "a" => Str::limit($text_side_effects, 250)];
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

    // Output combined schema array
    $schemas = [$drugSchema];
    if ($faqSchema) $schemas[] = $faqSchema;
    $schemaJson = json_encode($schemas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

@section('title',            "{$titleStr} | eHealthFinder")
@section('meta_description', $descStr)
@section('meta_keywords',    "{$brand->name}, {$generic}, {$brand->dosage_form} price Bangladesh, {$brand->company}, medicine Bangladesh")
@section('canonical',        $medUrl)
@section('og_type',          'product')
@section('og_title',         $titleStr)
@section('og_description',   $descStr)
@section('og_image',         $ogImg)

@section('schema')
<script type="application/ld+json">{!! $schemaJson !!}</script>
@endsection

@section('content')
<div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a> ›
    <a href="{{ route('medicines.index') }}">Medicines</a> ›
    <span>{{ $brand->name }}</span>
</div>
<style>
/* Floating Language Button */
.floating-lang-btn {
    position: fixed !important;
    bottom: 30px !important;
    right: 30px !important;
    z-index: 999999 !important;
    background: #008e76 !important;
    color: #ffffff !important;
    font-weight: 500 !important;
    font-size: 15px !important;
    padding: 10px 18px !important;
    border-radius: 4px !important;
    border: none !important;
    cursor: pointer !important;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    transition: background 0.2s !important;
    font-family: inherit !important;
}
.floating-lang-btn:hover {
    background: #00735f !important;
}
.floating-lang-btn svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
}

/* Info Section Block */
.info-section {
    background: #ffffff;
    padding: 2.2rem;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    margin-bottom: 2rem;
    line-height: 1.8;
    color: #334155;
    font-size: 1.05rem;
}
.info-section h4 {
    color: #1e40af;
    margin-top: 0;
    font-size: 1.35rem;
    margin-bottom: 1.2rem;
    border-bottom: 2px solid #eff6ff;
    padding-bottom: 0.8rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.empty-state {
    color: #94a3b8;
    font-style: italic;
}

/* 2-Column Grid Layout */
.med-main-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2.5rem;
    align-items: start;
}
@media (max-width: 900px) {
    .med-main-layout {
        grid-template-columns: 1fr;
    }
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Premium Hero Section */
.premium-med-hero {
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    border-radius: 20px;
    padding: 2.5rem;
    display: flex;
    flex-direction: row;
    align-items: stretch;
    gap: 3rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.03);
    margin-bottom: 3rem;
    border: 1px solid #dcfce7;
    position: relative;
    overflow: hidden;
}
.premium-med-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%);
    border-radius: 50%;
}
.med-image-container {
    flex: 0 0 240px;
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.04);
    border: 1px solid #f1f5f9;
}
.med-image-container img {
    max-width: 100%;
    height: auto;
    object-fit: contain;
    transition: transform 0.3s ease;
}
.med-image-container:hover img {
    transform: scale(1.05);
}
.med-info-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.med-title-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
}
.med-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    line-height: 1.2;
}
.antibiotic-badge {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #ef4444;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.med-dosage {
    font-size: 1.2rem;
    color: #64748b;
    margin-bottom: 1.5rem;
    font-weight: 500;
}
.med-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 2rem;
}
.med-tag {
    background: white;
    border: 1px solid #e2e8f0;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #334155;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.med-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.stat-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}
.stat-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #94a3b8;
    letter-spacing: 0.5px;
}
.stat-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
}
.stat-value.price {
    color: #10b981;
    font-size: 1.25rem;
}

@media (max-width: 768px) {
    .premium-med-hero {
        flex-direction: column;
        padding: 1.5rem;
        gap: 1.5rem;
    }
    .med-image-container {
        flex: none;
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
    }
    .med-title { font-size: 2rem; }
}
</style>

{{-- FIXED FLOATING LANG BUTTON --}}
<button id="langToggleBtn" class="floating-lang-btn" onclick="toggleLanguage()">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12.87 15.07l-2.54-2.51.03-.03A17.52 17.52 0 0 0 14.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04M18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12m-2.62 7l1.62-4.33L19.12 17h-3.24Z"/></svg>
    <span>বাংলায় দেখুন</span>
</button>

{{-- PREMIUM MEDICINE HERO --}}
<div class="premium-med-hero">
    {{-- Medicine Image --}}
    <div class="med-image-container" id="med-img-wrap">
        @if($brand->image_path)
            @php $safeImg = str_replace('\\', '/', $brand->image_path); @endphp
            <img onerror="this.outerHTML='💊'" id="med-thumb"
                 src="{{ Str::startsWith($safeImg,'http') ? $safeImg : asset($safeImg) }}"
                 alt="{{ $brand->name }}"
                 onmouseenter="showMedPreview(this)" onmouseleave="hideMedPreview()">
            {{-- Hover Preview --}}
            <div id="med-preview-box" style="display:none;position:fixed;z-index:10000;background:white;border-radius:16px;box-shadow:0 25px 80px rgba(0,0,0,0.25);border:1px solid #e2e8f0;padding:12px;pointer-events:none;">
                <img onerror="this.outerHTML='💊'" id="med-preview-img" src="{{ Str::startsWith($safeImg,'http') ? $safeImg : asset($safeImg) }}" style="width:320px;height:320px;object-fit:contain;border-radius:8px;display:block;" alt="{{ $brand->name }}">
            </div>
        @else
            <div style="font-size:4rem;color:#cbd5e1;">💊</div>
        @endif
    </div>

    {{-- Medicine Info --}}
    <div class="med-info-container">
        <div class="med-title-row">
            <h1 class="med-title">{{ $brand->name }}</h1>
            @if($brand->is_antibiotic)
                <span class="antibiotic-badge">⚠️ Antibiotic</span>
            @endif
        </div>
        <p class="med-dosage">{{ $brand->dosage_form }}</p>

        <div class="med-tags">
            @if($brand->generic)
                <span class="med-tag"><span style="color:#2563eb;">🧬</span> {{ $brand->generic->name }}</span>
            @endif
            @if($brand->company)
                <span class="med-tag"><span style="color:#64748b;">🏢</span> {{ $brand->company }}</span>
            @endif
        </div>

        <div class="med-stats-grid">
            <div class="stat-item">
                <span class="stat-label">Generic Name</span>
                <span class="stat-value">{{ $brand->generic->name ?? '—' }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Manufacturer</span>
                <span class="stat-value">{{ $brand->company ?: '—' }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Unit Price</span>
                <span class="stat-value price">{{ $brand->price ?: 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>

<script>
function showMedPreview(thumb) {
    const box = document.getElementById('med-preview-box');
    const rect = thumb.getBoundingClientRect();
    box.style.display = 'block';
    const top = Math.max(10, rect.top + rect.height/2 - 172);
    const left = rect.right + 20;
    box.style.top  = top + 'px';
    box.style.left = left + 'px';
    thumb.style.borderColor = 'rgba(255,255,255,0.9)';
}
function hideMedPreview() {
    document.getElementById('med-preview-box').style.display = 'none';
    const thumb = document.getElementById('med-thumb');
    if(thumb) thumb.style.borderColor = 'rgba(255,255,255,0.3)';
}
</script>


@php
    $sections = [
        'indications' => ['en' => 'Indications', 'bn' => 'নির্দেশনা'],
        'mode_of_action' => ['en' => 'Pharmacology', 'bn' => 'ফার্মাকোলজি'],
        'dosage' => ['en' => 'Dosage & Administration', 'bn' => 'মাত্রা ও ব্যবহারবিধি'],
        'interaction' => ['en' => 'Interaction', 'bn' => 'মিথস্ক্রিয়া'],
        'contraindications' => ['en' => 'Contraindications', 'bn' => 'প্রতিনির্দেশনা'],
        'side_effects' => ['en' => 'Side Effects', 'bn' => 'পার্শ্ব প্রতিক্রিয়া'],
        'pregnancy_cat' => ['en' => 'Pregnancy & Lactation', 'bn' => 'গর্ভাবস্থায় ও স্তন্যদানকালে'],
        'precautions' => ['en' => 'Precautions & Warnings', 'bn' => 'সতর্কতা'],
        'pediatric_uses' => ['en' => 'Pediatric Usage', 'bn' => 'শিশুদের ক্ষেত্রে ব্যবহার'],
        'storage_conditions' => ['en' => 'Storage Conditions', 'bn' => 'সংরক্ষণ'],
    ];
@endphp

<!-- CLINICAL DATA & ALTERNATIVES GRID -->
<div class="med-main-layout">

    <!-- LEFT COLUMN: Stacked Clinical Info -->
    <div class="med-details-column">
        @foreach($sections as $key => $title)
            @php 
                $enKey = $key . '_en';
                $bnKey = $key . '_bn';
                $enData = $brand->$enKey; 
                $bnData = $brand->$bnKey; 
            @endphp
            @if(!empty($enData) || !empty($bnData))
                <div class="info-section">
                    <h4 class="section-title">
                        <span style="font-size: 1.1em; color: #3b82f6;">🔹</span>
                        <span class="title-en">{{ $title['en'] }}</span>
                        <span class="title-bn" style="display:none; font-family: 'SolaimanLipi', sans-serif;">{{ $title['bn'] }}</span>
                    </h4>
                    
                    <div class="content-body-en">
                        {!! !empty($enData) ? $enData : '<span class="empty-state">Information not available in English.</span>' !!}
                    </div>
                    <div class="content-body-bn" style="display:none; font-family: 'SolaimanLipi', sans-serif;">
                        {!! !empty($bnData) ? $bnData : '<span class="empty-state">বাংলায় তথ্য পাওয়া যায়নি।</span>' !!}
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Dynamic FAQ Section -->
        @include('partials.faq-section')
        
        <!-- Polymorphic User Reviews & Ratings -->
        @include('partials.review-section', ['model' => $brand])
        
    </div>

    <!-- RIGHT COLUMN: Alternatives (Sticky Sidebar) -->
    <div class="med-sidebar-column">
        @if(count($alternatives) > 0)
            <div style="position: sticky; top: 120px; background: white; padding: 1.8rem; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.03);">
                <h3 style="margin-top: 0; font-size: 1.15rem; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 1rem; margin-bottom: 1.5rem; font-weight: 800;">
                    Alternative Medicines <br><span style="color:#64748b; font-size: 0.9rem; font-weight: 500;">(Same Generic)</span>
                </h3>
                <div style="display: flex; flex-direction: column; gap: 0.8rem;">
                    @foreach($alternatives as $alt)
                        <a href="{{ route('medicine.show', ['id' => $alt->id, 'slug' => Str::slug($alt->name)]) }}" 
                           style="display: block; padding: 1.2rem; border-radius: 12px; border: 1px solid #f8fafc; text-decoration: none; transition: all 0.2s; background: #f8fafc;">
                            <h4 style="margin: 0 0 0.4rem; color: #2563eb; font-weight: 700; font-size: 1.05rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                                {{ $alt->name }} 
                                <span style="background: white; border: 1px solid #e2e8f0; color: #64748b; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; white-space: nowrap;">{{ $alt->dosage_form }}</span>
                            </h4>
                            <p style="margin: 0 0 0.5rem; font-size: 0.9rem; color: #475569; font-weight: 500;">{{ $alt->company }}</p>
                            <p style="margin: 0; font-weight: 800; color: #10b981; font-size: 1.15rem;">{{ $alt->price }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</div>

<script>
let currentLang = 'en';

document.addEventListener('DOMContentLoaded', function() {
    // Move the button to body to escape CSS transform contexts that break position:fixed
    const btn = document.getElementById('langToggleBtn');
    if (btn) document.body.appendChild(btn);
});

function toggleLanguage() {
    currentLang = currentLang === 'en' ? 'bn' : 'en';
    const btn = document.getElementById('langToggleBtn');
    
    // SVG icon to keep in the button
    const svgIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12.87 15.07l-2.54-2.51.03-.03A17.52 17.52 0 0 0 14.07 6H17V4h-7V2H8v2H1v2h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04M18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12m-2.62 7l1.62-4.33L19.12 17h-3.24Z"/></svg>';
    
    if (currentLang === 'bn') {
        btn.innerHTML = svgIcon + '<span>View in English</span>';
        
        document.querySelectorAll('.title-en, .content-body-en').forEach(el => el.style.display = 'none');
        
        // Show Bangla titles inline
        document.querySelectorAll('.title-bn').forEach(el => el.style.display = 'inline');
        // Show Bangla content as block
        document.querySelectorAll('.content-body-bn').forEach(el => el.style.display = 'block');
    } else {
        btn.innerHTML = svgIcon + '<span>বাংলায় দেখুন</span>';
        
        // Show English titles inline
        document.querySelectorAll('.title-en').forEach(el => el.style.display = 'inline');
        // Show English content as block
        document.querySelectorAll('.content-body-en').forEach(el => el.style.display = 'block');
        
        document.querySelectorAll('.title-bn, .content-body-bn').forEach(el => el.style.display = 'none');
    }
}
</script>
@endsection
