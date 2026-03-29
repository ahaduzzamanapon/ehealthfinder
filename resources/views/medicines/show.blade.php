@extends('layouts.app')

@php
    $generic  = $brand->generic?->name ?? '';
    $medUrl   = route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug]);
    $safeImg  = str_replace('\\', '/', $brand->image_path ?? '');
    $imgUrl   = $safeImg ? (Str::startsWith($safeImg, 'http') ? $safeImg : asset($safeImg)) : null;
    $ogImg    = $imgUrl ?? asset('logo.png');
    $titleStr = "{$brand->name} {$brand->dosage_form} \u2013 Price, Uses & Side Effects";
    $descStr  = "{$brand->name} {$brand->dosage_form} by {$brand->company}. Generic: {$generic}. Price: {$brand->price}. Find indications, dosage, side effects and alternatives on eHealthFinder.";

    // Build JSON-LD entirely in PHP to avoid @if inside @section
    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'Drug',
        'name'            => $brand->name,
        'description'     => $descStr,
        '@id'             => $medUrl,
        'url'             => $medUrl,
        'activeIngredient'=> $generic,
        'dosageForm'      => $brand->dosage_form,
        'manufacturer'    => ['@type' => 'Organization', 'name' => $brand->company],
    ];
    if ($imgUrl) $schema['image'] = $imgUrl;
    if ($brand->price) {
        $schema['offers'] = [
            '@type'        => 'Offer',
            'price'        => $brand->price,
            'priceCurrency'=> 'BDT',
            'availability' => 'https://schema.org/InStock',
        ];
    }
    $schemaJson = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
/* Glassmorphism Tabs Styles */
.glass-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    background: rgba(255, 255, 255, 0.4);
    padding: 0.5rem;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
}
.glass-tab {
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    color: var(--text);
    transition: all 0.3s ease;
    border: 1px solid transparent;
    font-size: 0.95rem;
    user-select: none;
}
.glass-tab:hover {
    background: rgba(255, 255, 255, 0.6);
}
.glass-tab.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 4px 15px rgba(26, 115, 232, 0.3);
}
.glass-content {
    display: none;
    background: rgba(255, 255, 255, 0.7);
    padding: 2.5rem;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    animation: fadeIn 0.4s ease;
    line-height: 1.8;
    color: var(--text);
    font-size: 1.05rem;
}
.glass-content.active {
    display: block;
}
.glass-content h4 {
    color: var(--primary-dark);
    margin-top: 0;
    font-size: 1.4rem;
    margin-bottom: 1rem;
    border-bottom: 2px solid rgba(26, 115, 232, 0.1);
    padding-bottom: 0.5rem;
}

/* Language Toggle Switch */
.lang-toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(255, 255, 255, 0.7);
    padding: 0.6rem 1.2rem;
    border-radius: 50px;
    border: 1px solid rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}
.switch input { 
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    transition: .4s;
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
input:checked + .slider {
    background-color: #10b981; /* Green for Bangla */
}
input:checked + .slider:before {
    transform: translateX(26px);
}
.lang-label {
    font-weight: 700;
    color: #64748b;
    transition: color 0.3s;
    font-size: 1.1rem;
}
.lang-label.en.active { color: var(--primary); }
.lang-label.bn.active { color: #10b981; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.empty-state {
    color: #64748b;
    font-style: italic;
}
</style>

<div class="bc" style="margin-bottom: 1.5rem">
    <a href="{{ route('medicines.index') }}" style="color:var(--text); text-decoration:underline">Medicines</a> &raquo;
    <strong>{{ $brand->name }}</strong>
</div>

<!-- TOP HERO CARD -->
<div class="card" style="margin-bottom: 2rem; display:flex; flex-wrap:wrap; gap: 3rem; position:relative;">
    
    <!-- Lang Switcher Positioned Top Right -->
    <div style="position: absolute; top: 1.5rem; right: 1.5rem;" class="lang-toggle-wrapper">
        <span class="lang-label en active" id="label-en">EN</span>
        <label class="switch">
            <input type="checkbox" id="langToggle">
            <span class="slider"></span>
        </label>
        <span class="lang-label bn" id="label-bn">বাং</span>
    </div>

    @if($brand->image_path)
    <div style="flex: 0 0 140px; max-width:140px; position:relative;" id="med-img-wrap">
        @php $safeImg = str_replace('\\', '/', $brand->image_path); @endphp
        <img id="med-thumb"
             src="{{ Str::startsWith($safeImg, 'http') ? $safeImg : asset($safeImg) }}" 
             style="width: 140px; height: 140px; border-radius: 12px; border: 2px solid var(--gray); box-shadow: var(--shadow); object-fit: contain; background: white; cursor: zoom-in; transition: border-color 0.2s;" 
             alt="{{ $brand->name }}"
             onmouseenter="showMedPreview(this)" onmouseleave="hideMedPreview()">

        <!-- Hover Preview -->
        <div id="med-preview-box" style="
            display:none;
            position:fixed;
            z-index:9999;
            background:white;
            border-radius:16px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.25);
            border: 1px solid #e2e8f0;
            padding: 12px;
            pointer-events:none;
        ">
            <img id="med-preview-img"
                 src="{{ Str::startsWith($safeImg, 'http') ? $safeImg : asset($safeImg) }}"
                 style="width:320px; height:320px; object-fit:contain; border-radius:8px; display:block;"
                 alt="{{ $brand->name }}">
        </div>
    </div>
    @endif

    <script>
    function showMedPreview(thumb) {
        const box = document.getElementById('med-preview-box');
        const rect = thumb.getBoundingClientRect();
        box.style.display = 'block';
        // Position: right of the thumb + 20px gap, vertically centered
        const top = Math.max(10, rect.top + rect.height/2 - 172);
        const left = rect.right + 20;
        box.style.top  = top + 'px';
        box.style.left = left + 'px';
        thumb.style.borderColor = 'var(--primary)';
    }
    function hideMedPreview() {
        document.getElementById('med-preview-box').style.display = 'none';
        const thumb = document.getElementById('med-thumb');
        if(thumb) thumb.style.borderColor = 'var(--gray)';
    }
    </script>
    
    <div style="flex: 1; min-width: 300px; padding-top: 1rem;">
        <div style="display:flex; align-items:center; gap: 1rem; margin-bottom: 0.5rem">
            <h1 style="margin: 0; font-size: 2.5rem; color: var(--primary-dark)">{{ $brand->name }}</h1>
            @if($brand->is_antibiotic)
                <span style="background:#fed7d7; color:#c53030; font-size: 0.9rem; padding: 4px 12px; border-radius: 50px; font-weight:700; box-shadow: 0 2px 4px rgba(197,48,48,0.2)">Antibiotic</span>
            @endif
        </div>
        
        <h3 style="color: var(--text); font-weight: 500; margin-top:0; font-size: 1.2rem;">{{ $brand->dosage_form }}</h3>
        
        <div style="margin-top: 2rem; background: rgba(248, 250, 252, 0.6); padding: 1.5rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.8); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div>
                <strong style="color: var(--text-light); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Generic Name</strong> <br>
                <span style="color: var(--primary); font-size: 1.3rem; font-weight: 700">{{ $brand->generic->name ?? 'Unknown' }}</span>
            </div>
            <div>
                <strong style="color: var(--text-light); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Manufacturer</strong> <br>
                <span style="font-size: 1.1rem; font-weight: 600; color: var(--text);">{{ $brand->company }}</span>
            </div>
            <div>
                <strong style="color: var(--text-light); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Unit Price</strong> <br>
                <span style="font-size: 1.6rem; font-weight: 800; color: #10b981">{{ $brand->price }}</span>
            </div>
        </div>
    </div>
</div>

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

<!-- CLINICAL DATA TABBED INTERFACE -->
<div style="margin-bottom: 3rem;">
    <!-- Tab Headers -->
    <div class="glass-tabs" id="tabMenu">
        @php $isFirstTab = true; @endphp
        @foreach($sections as $key => $title)
            @php 
                $enKey = $key . '_en';
                $bnKey = $key . '_bn';
                $enData = $brand->$enKey; 
                $bnData = $brand->$bnKey; 
            @endphp
            @if(!empty($enData) || !empty($bnData))
                <div class="glass-tab {{ $isFirstTab ? 'active' : '' }}" data-tab="{{ $key }}" onclick="openTab(this.dataset.tab, event)">
                    <span class="tab-title-en">{{ $title['en'] }}</span>
                    <span class="tab-title-bn" style="display:none">{{ $title['bn'] }}</span>
                </div>
                @php $isFirstTab = false; @endphp
            @endif
        @endforeach
    </div>

    <!-- Tab Contents -->
    <div id="tabContentContainer">
        @php $isFirstContent = true; @endphp
        @foreach($sections as $key => $title)
            @php 
                $enKey = $key . '_en';
                $bnKey = $key . '_bn';
                $enData = $brand->$enKey; 
                $bnData = $brand->$bnKey; 
            @endphp
            @if(!empty($enData) || !empty($bnData))
                <div id="tab-{{ $key }}" class="glass-content {{ $isFirstContent ? 'active' : '' }}">
                    <h4 class="content-title-en">{{ $title['en'] }}</h4>
                    <h4 class="content-title-bn" style="display:none; font-family: 'SolaimanLipi', sans-serif;">{{ $title['bn'] }}</h4>
                    
                    <div class="content-body-en">
                        {!! !empty($enData) ? $enData : '<span class="empty-state">Information not available in English.</span>' !!}
                    </div>
                    <div class="content-body-bn" style="display:none; font-family: 'SolaimanLipi', sans-serif;">
                        {!! !empty($bnData) ? $bnData : '<span class="empty-state">বাংলায় তথ্য পাওয়া যায়নি।</span>' !!}
                    </div>
                </div>
                @php $isFirstContent = false; @endphp
            @endif
        @endforeach
    </div>
</div>

<!-- ALTERNATIVES SECTION -->
@if(count($alternatives) > 0)
    <h2 style="margin-bottom: 1.5rem; color: var(--text); border-bottom: 2px solid rgba(0,0,0,0.05); padding-bottom: 0.5rem;">Alternative Medicines <span style="color:var(--text-light); font-size: 1.2rem; font-weight: normal;">(Same Generic)</span></h2>
    <div class="grid-3">
        @foreach($alternatives as $alt)
            <a href="{{ route('medicine.show', ['id' => $alt->id, 'slug' => Str::slug($alt->name)]) }}" style="color:inherit; text-decoration:none;">
                <div class="card" style="padding: 1.5rem; transition: transform 0.2s; border-left: 4px solid var(--primary)">
                    <h3 style="margin-top:0; margin-bottom: 0.3rem; color:var(--primary-dark)">{{ $alt->name }} <span style="background:var(--bg); color:var(--text-light); padding:2px 8px; border-radius:12px; font-size:0.8rem; margin-left: 6px;">{{ $alt->dosage_form }}</span></h3>
                    <p style="margin-bottom:0.8rem; font-size:0.9rem; color: var(--text);"><strong>{{ $alt->company }}</strong></p>
                    <p style="margin:0; font-weight:800; color:#10b981; font-size: 1.2rem;">{{ $alt->price }}</p>
                </div>
            </a>
        @endforeach
    </div>
@endif

<script>
// Tab Switching Logic
function openTab(tabId, ev) {
    // Remove active class from all tabs
    document.querySelectorAll('.glass-tab').forEach(tab => tab.classList.remove('active'));
    // Remove active class from all contents
    document.querySelectorAll('.glass-content').forEach(content => content.classList.remove('active'));
    
    // Add active class to clicked tab (using event target)
    ev.currentTarget.classList.add('active');
    // Add active class to corresponding content
    document.getElementById('tab-' + tabId).classList.add('active');
}

// Language Toggle Logic
const langToggle = document.getElementById('langToggle');
const labelEn = document.getElementById('label-en');
const labelBn = document.getElementById('label-bn');

langToggle.addEventListener('change', function() {
    const isBn = this.checked;
    
    // Toggle Labels
    if(isBn) {
        labelBn.classList.add('active');
        labelEn.classList.remove('active');
    } else {
        labelEn.classList.add('active');
        labelBn.classList.remove('active');
    }
    
    // Toggle ALL EN / BN elements inside Tabs
    document.querySelectorAll('.tab-title-en').forEach(el => el.style.display = isBn ? 'none' : 'inline');
    document.querySelectorAll('.tab-title-bn').forEach(el => el.style.display = isBn ? 'inline' : 'none');
    
    // Toggle ALL EN / BN elements inside Contents
    document.querySelectorAll('.content-title-en').forEach(el => el.style.display = isBn ? 'none' : 'block');
    document.querySelectorAll('.content-title-bn').forEach(el => el.style.display = isBn ? 'block' : 'none');
    
    document.querySelectorAll('.content-body-en').forEach(el => el.style.display = isBn ? 'none' : 'block');
    document.querySelectorAll('.content-body-bn').forEach(el => el.style.display = isBn ? 'block' : 'none');
});
</script>
@endsection
