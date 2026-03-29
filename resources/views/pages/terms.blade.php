@extends('layouts.app')

@section('title', 'Terms of Use | eHealthFinder Bangladesh')
@section('meta_description', 'Read the Terms of Use for eHealthFinder. By using our platform, you agree to these terms governing your access to our healthcare information services.')
@section('og_title', 'Terms of Use | eHealthFinder')
@section('canonical', route('terms'))

@section('content')
<div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a> ›
    <span>Terms of Use</span>
</div>

<div class="info-card" style="max-width:860px; margin:0 auto;">
    <div style="text-align:center; margin-bottom:2.5rem;">
        <div style="font-size:3.5rem; margin-bottom:1rem;">📋</div>
        <h1 style="font-size:2.2rem; color:var(--primary-dark); margin-bottom:0.5rem;">Terms of Use</h1>
        <p style="color:var(--text-light);">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <p>By accessing and using <strong>ehealthfinder.com</strong> ("the Site"), you agree to be bound by these Terms of Use. If you do not agree with any part of these terms, please do not use this website.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">1. Acceptance of Terms</h2>
    <p>Your use of eHealthFinder constitutes your acceptance of these Terms of Use. We reserve the right to modify these terms at any time. Continued use of the site after any changes constitutes acceptance of the new terms.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">2. Use of the Platform</h2>
    <p>You agree to use eHealthFinder only for lawful purposes and in a way that does not infringe the rights of others. You must not:</p>
    <ul style="line-height:2; padding-left:1.5rem;">
        <li>Use the site in any way that violates applicable laws or regulations in Bangladesh.</li>
        <li>Attempt to gain unauthorized access to any part of our systems.</li>
        <li>Transmit any unsolicited or unauthorized advertising material.</li>
        <li>Scrape, crawl, or extract data from the site without prior written permission.</li>
        <li>Misrepresent yourself or impersonate any person or entity.</li>
    </ul>

    <h2 style="color:var(--primary); margin-top:2rem;">3. Intellectual Property</h2>
    <p>All content on eHealthFinder — including text, design, logos, graphics, and code — is the property of eHealthFinder or its content suppliers and is protected by applicable copyright and intellectual property laws. You may not reproduce, distribute, or create derivative works without explicit permission.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">4. Doctor Profiles</h2>
    <p>Doctor profiles are compiled from publicly available information. If you are a medical professional and wish to update, correct, or remove your profile, please contact us at <a href="mailto:contact@ehealthfinder.com">contact@ehealthfinder.com</a>. We will process all legitimate requests within a reasonable timeframe.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">5. Disclaimer of Warranties</h2>
    <p>The site is provided on an "as is" and "as available" basis. eHealthFinder makes no warranties, express or implied, regarding the accuracy, completeness, or reliability of any content on the site.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">6. Limitation of Liability</h2>
    <p>To the fullest extent permitted by law, eHealthFinder shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of, or inability to use, the site.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">7. Third-Party Links</h2>
    <p>Our site may contain links to third-party websites. These links are provided for your convenience only. We have no control over the content of those sites and take no responsibility for them.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">8. Governing Law</h2>
    <p>These Terms are governed by and construed in accordance with the laws of the <strong>People's Republic of Bangladesh</strong>. Any disputes shall be subject to the exclusive jurisdiction of the courts of Bangladesh.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">9. Contact</h2>
    <p>For any questions about these Terms, please contact us at <a href="mailto:contact@ehealthfinder.com">contact@ehealthfinder.com</a>.</p>

    <div style="margin-top:3rem; border-top:1px solid var(--gray); padding-top:1.5rem; text-align:center; color:var(--text-light); font-size:0.9rem;">
        <p>By using eHealthFinder, you acknowledge that you have read, understood, and agree to be bound by these Terms of Use.</p>
    </div>
</div>
@endsection
