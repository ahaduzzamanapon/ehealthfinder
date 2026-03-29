@extends('layouts.app')

@section('title', 'Privacy Policy | eHealthFinder Bangladesh')
@section('meta_description', 'Read eHealthFinder\'s Privacy Policy to understand how we collect, use and protect your personal information.')
@section('og_title', 'Privacy Policy | eHealthFinder')
@section('canonical', route('privacy'))

@section('content')
<div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a> ›
    <span>Privacy Policy</span>
</div>

<div class="info-card" style="max-width:860px; margin:0 auto;">
    <div style="text-align:center; margin-bottom:2.5rem;">
        <div style="font-size:3.5rem; margin-bottom:1rem;">🔒</div>
        <h1 style="font-size:2.2rem; color:var(--primary-dark); margin-bottom:0.5rem;">Privacy Policy</h1>
        <p style="color:var(--text-light);">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <p>Welcome to <strong>eHealthFinder</strong> ("we," "our," or "us"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website <strong>ehealthfinder.com</strong>. Please read this policy carefully.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">1. Information We Collect</h2>
    <p>We may collect the following types of information:</p>
    <ul style="line-height:2; padding-left:1.5rem;">
        <li><strong>Usage Data:</strong> Pages visited, search queries, time spent, and browser/device information collected automatically via server logs and analytics tools.</li>
        <li><strong>Search Queries:</strong> Doctor names, specialties, and locations you search for, to improve our search results.</li>
        <li><strong>Contact Information:</strong> If you contact us via email, we collect your email address and message content.</li>
    </ul>
    <p>We do <strong>not</strong> collect sensitive personal health data or require user registration to use this platform.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">2. How We Use Your Information</h2>
    <ul style="line-height:2; padding-left:1.5rem;">
        <li>To operate, maintain, and improve the eHealthFinder platform.</li>
        <li>To analyze usage patterns and enhance search accuracy.</li>
        <li>To respond to your inquiries and provide customer support.</li>
        <li>To detect and prevent technical issues or abuse.</li>
    </ul>

    <h2 style="color:var(--primary); margin-top:2rem;">3. Cookies</h2>
    <p>We use cookies and similar tracking technologies to improve your browsing experience. These may include session cookies (deleted when you close your browser) and persistent cookies (remain for a set period). You can disable cookies in your browser settings, though some features may not function properly.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">4. Third-Party Services</h2>
    <p>We may use third-party analytics tools (such as Google Analytics) that collect anonymized usage data. These services have their own privacy policies. We do not sell your data to third parties.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">5. Data Security</h2>
    <p>We implement reasonable technical and organizational measures to protect your information. However, no internet transmission is 100% secure. We encourage you not to share sensitive personal information through this platform.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">6. Children's Privacy</h2>
    <p>eHealthFinder is not directed to children under 13. We do not knowingly collect personal information from children.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">7. Changes to This Policy</h2>
    <p>We may update this Privacy Policy from time to time. The "Last updated" date at the top will reflect any changes. Continued use of the site constitutes acceptance of the updated policy.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">8. Contact Us</h2>
    <p>For privacy-related questions, please email us at <a href="mailto:contact@ehealthfinder.com">contact@ehealthfinder.com</a>.</p>
</div>
@endsection
