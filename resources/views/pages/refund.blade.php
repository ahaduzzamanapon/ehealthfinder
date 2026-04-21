@extends('layouts.app')

@section('title', 'Return & Refund Policy | eHealthFinder')
@section('meta_description', 'Read the Return and Refund Policy of eHealthFinder. Understand our medicine purchase return conditions, refund process, and customer rights in Bangladesh.')
@section('canonical', route('refund'))

@section('content')
<style>
.policy-hero {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    padding: 4rem 2rem;
    text-align: center;
    margin-bottom: 3rem;
}
.policy-hero h1 { font-size: 2.5rem; font-weight: 800; margin: 0 0 0.75rem; }
.policy-hero p  { font-size: 1.1rem; opacity: 0.85; margin: 0; }

.policy-body {
    max-width: 860px;
    margin: 0 auto 4rem;
    padding: 0 1.5rem;
}
.policy-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.policy-section h2 {
    font-size: 1.3rem;
    font-weight: 800;
    color: #1e1b4b;
    margin: 0 0 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.policy-section p, .policy-section li {
    color: #475569;
    line-height: 1.85;
    font-size: 1rem;
    margin-bottom: 0.75rem;
}
.policy-section ul { padding-left: 1.5rem; }
.policy-section li { margin-bottom: 0.5rem; }
.highlight-box {
    background: #eff6ff;
    border-left: 4px solid #4f46e5;
    border-radius: 0 12px 12px 0;
    padding: 1rem 1.5rem;
    margin: 1rem 0;
    color: #1e40af;
    font-weight: 600;
}
.step-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
.step-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.2rem;
    text-align: center;
}
.step-num {
    background: #4f46e5;
    color: white;
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.9rem;
    margin: 0 auto 0.75rem;
}
.step-card p { font-size: 0.9rem; color: #475569; margin: 0; }
.contact-box {
    background: linear-gradient(135deg, #f0fdf4, #eff6ff);
    border: 1px solid #dcfce7;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    margin-top: 2rem;
}
.contact-box h3 { color: #1e1b4b; font-weight: 800; margin: 0 0 0.5rem; }
.contact-box p { color: #475569; margin: 0 0 1rem; }
.contact-box a {
    display: inline-block;
    background: #4f46e5;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 50px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s;
}
.contact-box a:hover { background: #3730a3; }
</style>

<div class="policy-hero">
    <h1>↩️ Return & Refund Policy</h1>
    <p>Last Updated: {{ date('F d, Y') }} &nbsp;|&nbsp; Effective: Immediately</p>
</div>

<div class="policy-body">

    <div class="policy-section">
        <h2>📋 Overview</h2>
        <p>At <strong>eHealthFinder</strong>, we are committed to providing accurate medicine information and a transparent shopping experience. This Return & Refund Policy explains the conditions under which you may return a purchased product and receive a refund.</p>
        <div class="highlight-box">
            🛡️ We follow all guidelines of the <strong>Directorate General of Drug Administration (DGDA), Bangladesh</strong> and comply with applicable consumer protection laws.
        </div>
    </div>

    <div class="policy-section">
        <h2>✅ Eligibility for Return</h2>
        <p>You are eligible to return a product within <strong>7 days</strong> of delivery if:</p>
        <ul>
            <li>The product delivered is <strong>incorrect</strong> (different brand, strength, or dosage form than ordered).</li>
            <li>The product is <strong>damaged or defective</strong> upon arrival — visibly broken packaging, contamination, or missing items.</li>
            <li>The product is <strong>expired</strong> or has a manufacture date discrepancy.</li>
            <li>The product was <strong>not delivered</strong> but marked as delivered.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>❌ Non-Returnable Items</h2>
        <p>The following items <strong>cannot be returned</strong> under any circumstances:</p>
        <ul>
            <li>Medicines that have been <strong>opened, used, or partially consumed</strong>.</li>
            <li>Temperature-sensitive products (vaccines, insulin) once removed from cold storage.</li>
            <li>Products purchased at a <strong>discounted/clearance</strong> sale, unless defective.</li>
            <li>Products returned after the <strong>7-day window</strong> without prior approval.</li>
            <li>Prescription medicines where the prescription has already been dispensed.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>💰 Refund Policy</h2>
        <p>Once your return is <strong>approved and received</strong>, your refund will be processed as follows:</p>
        <ul>
            <li><strong>Full Refund</strong>: If the item is defective, expired, or incorrect — 100% refund including delivery charge.</li>
            <li><strong>Partial Refund</strong>: If packaging is damaged but product is intact and correct — refund subject to inspection.</li>
            <li><strong>Store Credit</strong>: Available as an alternative to cash refund at your choice.</li>
        </ul>
        <div class="highlight-box">
            ⏱️ Refunds are typically processed within <strong>5–7 business days</strong> after we receive and inspect the returned item.
        </div>
        <p>Refunds will be credited to your <strong>original payment method</strong>: bKash, Nagad, card, or bank transfer.</p>
    </div>

    <div class="policy-section">
        <h2>🔄 How to Request a Return</h2>
        <div class="step-grid">
            <div class="step-card">
                <div class="step-num">1</div>
                <strong>Contact Us</strong>
                <p>Email or call within 7 days of delivery.</p>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <strong>Provide Details</strong>
                <p>Share your order ID, product photo, and reason.</p>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <strong>Approval</strong>
                <p>We review and send a return authorization within 24–48 hrs.</p>
            </div>
            <div class="step-card">
                <div class="step-num">4</div>
                <strong>Ship Back</strong>
                <p>Send the product to our address via courier.</p>
            </div>
            <div class="step-card">
                <div class="step-num">5</div>
                <strong>Refund Issued</strong>
                <p>Refund processed within 5–7 business days.</p>
            </div>
        </div>
    </div>

    <div class="policy-section">
        <h2>🚚 Return Shipping</h2>
        <ul>
            <li>If the return is due to <strong>our error</strong> (wrong or defective product), we will bear the return shipping cost.</li>
            <li>If the return is for any other reason, the customer is responsible for return shipping charges.</li>
            <li>Please use a <strong>trackable courier service</strong> and retain your shipping receipt.</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>📍 Return Address</h2>
        <p><strong>eHealthFinder</strong><br>
        Bangladesh Healthcare Portal<br>
        Email: <a href="mailto:contact@ehealthfinder.com">contact@ehealthfinder.com</a><br>
        Please do <strong>not</strong> ship any return without prior email authorization.</p>
    </div>

    <div class="policy-section">
        <h2>⚖️ Dispute Resolution</h2>
        <p>If you are not satisfied with our return/refund decision, you may escalate the issue to:</p>
        <ul>
            <li>The <strong>Directorate of National Consumer Rights Protection (DNCRP), Bangladesh</strong></li>
            <li>Or contact us directly at <a href="mailto:contact@ehealthfinder.com">contact@ehealthfinder.com</a> for further review.</li>
        </ul>
        <p>This policy is governed by the laws of the <strong>People's Republic of Bangladesh</strong>.</p>
    </div>

    <div class="contact-box">
        <h3>Need Help with a Return?</h3>
        <p>Our support team is ready to assist you Monday–Saturday, 9 AM – 6 PM.</p>
        <a href="mailto:contact@ehealthfinder.com">✉️ Email Support</a>
    </div>

</div>
@endsection
