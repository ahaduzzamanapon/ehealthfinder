@extends('layouts.app')

@section('title', 'Disclaimer | eHealthFinder Bangladesh')
@section('meta_description', 'Read eHealthFinder\'s medical disclaimer. Information on this site is for general informational purposes only and does not constitute medical advice.')
@section('og_title', 'Disclaimer | eHealthFinder')
@section('canonical', route('disclaimer'))

@section('content')
<div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a> ›
    <span>Disclaimer</span>
</div>

<div class="info-card" style="max-width:860px; margin:0 auto;">
    <div style="text-align:center; margin-bottom:2.5rem;">
        <div style="font-size:3.5rem; margin-bottom:1rem;">⚠️</div>
        <h1 style="font-size:2.2rem; color:var(--primary-dark); margin-bottom:0.5rem;">Disclaimer</h1>
        <p style="color:var(--text-light);">Last updated: {{ date('F d, Y') }}</p>
    </div>

    <div style="background:#fef3c7; border-left:4px solid #f59e0b; border-radius:8px; padding:1.2rem 1.5rem; margin-bottom:2rem;">
        <p style="margin:0; font-weight:600; color:#92400e;">⚕️ Important Medical Notice: The information on eHealthFinder is for general informational purposes only and does not constitute professional medical advice, diagnosis, or treatment.</p>
    </div>

    <h2 style="color:var(--primary); margin-top:2rem;">1. Not Medical Advice</h2>
    <p>All content on <strong>ehealthfinder.com</strong>, including doctor profiles, medicine information, indications, dosages, and side effects, is provided for <strong>informational purposes only</strong>. This information is <strong>not a substitute</strong> for professional medical advice, diagnosis, or treatment by a qualified healthcare provider.</p>
    <p>Always consult a qualified doctor or pharmacist regarding any medical condition, medication, or treatment plan. Never disregard professional medical advice or delay seeking it because of information you have read on this website.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">2. Accuracy of Information</h2>
    <p>While we strive to keep the information accurate and up-to-date, eHealthFinder makes no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, or suitability of the information. Medicine prices, doctor availability, chamber timings, and appointment numbers may change without notice. Always verify directly with the doctor or pharmacy.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">3. Doctor Profile Information</h2>
    <p>Doctor profiles on eHealthFinder are compiled from publicly available sources. We do not independently verify every detail. Doctors listed on this platform have not necessarily endorsed or partnered with eHealthFinder. If you are a doctor and wish to update or remove your profile, please contact us.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">4. Medicine Information</h2>
    <p>Medicine data including indications, dosage, side effects, and interactions is sourced from official pharmaceutical references. This information is for educational purposes. Dosage requirements vary by individual patient factors. Always follow your doctor's prescription and the pharmacist's guidance.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">5. Emergency Situations</h2>
    <p>If you are experiencing a medical emergency, call <strong>999</strong> (Bangladesh National Emergency Service) or go to your nearest hospital immediately. This website is not equipped to handle medical emergencies.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">6. External Links</h2>
    <p>Our website may contain links to external sites. We have no control over the content of those sites and accept no responsibility for them or for any loss or damage that may arise from your use of them.</p>

    <h2 style="color:var(--primary); margin-top:2rem;">7. Limitation of Liability</h2>
    <p>eHealthFinder shall not be liable for any direct, indirect, incidental, or consequential damages arising from the use or inability to use this website or its content.</p>
</div>
@endsection
