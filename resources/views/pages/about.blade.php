@extends('layouts.app')

@section('title', 'About Us | eHealthFinder Bangladesh')
@section('meta_description', 'eHealthFinder is Bangladesh\'s leading healthcare portal connecting patients with verified specialist doctors and comprehensive medicine information.')
@section('og_title', 'About Us | eHealthFinder')
@section('canonical', route('about'))

@section('content')
<div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a> ›
    <span>About Us</span>
</div>

<div class="info-card" style="max-width:860px; margin:0 auto;">
    <div style="text-align:center; margin-bottom:2.5rem;">
        <div style="font-size:3.5rem; margin-bottom:1rem;">🏥</div>
        <h1 style="font-size:2.2rem; color:var(--primary-dark); margin-bottom:0.5rem;">About eHealthFinder</h1>
        <p style="color:var(--text-light); font-size:1.05rem;">Bangladesh's trusted healthcare discovery platform</p>
    </div>

    <h2 style="color:var(--primary); margin-top:2rem;">Who We Are</h2>
    <p>eHealthFinder is Bangladesh's leading online healthcare portal, dedicated to bridging the gap between patients and qualified medical professionals. We provide a comprehensive, easy-to-use platform where anyone in Bangladesh can find verified specialist doctors, access detailed medicine information, and make informed healthcare decisions.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">Our Mission</h2>
    <p>Our mission is to make quality healthcare accessible to every Bangladeshi citizen. We believe that finding the right doctor or understanding your medication should never be a challenge. Through technology and data, we empower patients with the information they need to take control of their health.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">What We Offer</h2>
    <ul style="line-height:2; padding-left:1.5rem;">
        <li><strong>Doctor Directory:</strong> Over 7,000 verified specialist doctors across Bangladesh with chamber details, visiting hours and appointment numbers.</li>
        <li><strong>Medicine Index:</strong> Comprehensive database of over 1,600 medicine brands with generic names, dosage, side effects, indications and pricing.</li>
        <li><strong>Smart Search:</strong> Find the right specialist in your city with our intelligent search—try "Cardiologist in Dhaka" or "Cancer Surgeon in Chittagong."</li>
        <li><strong>SEO-Optimized Profiles:</strong> Each doctor and medicine has a dedicated, search-engine-optimized page for easy discovery.</li>
    </ul>

    <h2 style="color:var(--primary); margin-top:2rem;">Our Data</h2>
    <p>All doctor profiles and medicine data on eHealthFinder are sourced from publicly available medical directories and official pharmaceutical references in Bangladesh. We continuously update our database to ensure accuracy. If you find any inaccurate information, please contact us so we can correct it promptly.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">Contact Us</h2>
    <p>We'd love to hear from you. Whether you have a question, feedback, or would like to claim/update a doctor profile, feel free to reach out.</p>
    <div style="background:rgba(79,70,229,0.06); border-left:4px solid var(--primary); border-radius:8px; padding:1.2rem 1.5rem; margin-top:1rem;">
        <p style="margin:0;">📧 <strong>Email:</strong> contact@ehealthfinder.com<br>
        🌐 <strong>Website:</strong> https://ehealthfinder.com<br>
        📍 <strong>Based in:</strong> Dhaka, Bangladesh</p>
    </div>
</div>
@endsection
