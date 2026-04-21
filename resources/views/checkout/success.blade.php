@extends('layouts.app')

@section('title', 'Order Placed Successfully | eHealthFinder')
@section('meta_description', 'Your medicine order has been placed successfully. Thank you for shopping with eHealthFinder.')

@section('content')
<style>
.success-wrap {
    max-width: 680px;
    margin: 3rem auto 5rem;
    padding: 0 1.25rem;
    text-align: center;
}

/* Animated checkmark */
.success-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, #10b981, #059669);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem;
    margin: 0 auto 2rem;
    box-shadow: 0 12px 40px rgba(16,185,129,.35);
    animation: popIn .5s cubic-bezier(.175,.885,.32,1.275) both;
}
@keyframes popIn {
    from { transform: scale(0); opacity: 0; }
    to   { transform: scale(1); opacity: 1; }
}

.success-title {
    font-size: 2.2rem; font-weight: 900; color: #1e1b4b; margin: 0 0 .75rem;
    animation: fadeUp .5s .15s both;
}
.success-sub {
    font-size: 1.05rem; color: #64748b; margin: 0 0 2.5rem;
    animation: fadeUp .5s .25s both;
}
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Order Card */
.order-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    text-align: left;
    margin-bottom: 1.5rem;
    animation: fadeUp .5s .35s both;
}
.order-card h3 {
    font-size: 1rem; font-weight: 800; color: #1e1b4b;
    margin: 0 0 1.25rem; padding-bottom: .75rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: .5rem;
}
.detail-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: .6rem 0; border-bottom: 1px solid #f8fafc;
    font-size: .95rem;
}
.detail-row:last-child { border-bottom: none; }
.detail-row .label { color: #64748b; font-weight: 600; }
.detail-row .value { font-weight: 700; color: #1e293b; }
.detail-row .value.green { color: #10b981; }
.detail-row .value.purple { color: #4f46e5; }

/* Status Timeline */
.timeline { margin: 0; padding: 0; list-style: none; }
.timeline li {
    display: flex; gap: 1rem; align-items: flex-start;
    padding-bottom: 1.25rem; position: relative;
}
.timeline li:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 15px; top: 32px;
    width: 2px; height: calc(100% - 10px);
    background: #e2e8f0;
}
.tl-dot {
    width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; position: relative; z-index: 1;
}
.tl-dot.done  { background: #dcfce7; color: #16a34a; }
.tl-dot.active{ background: #eff6ff; color: #4f46e5; border: 2px solid #4f46e5; }
.tl-dot.wait  { background: #f1f5f9; color: #94a3b8; }
.tl-info strong { font-size: .95rem; color: #1e293b; display: block; margin-bottom: .15rem; }
.tl-info span { font-size: .82rem; color: #94a3b8; }

/* Actions */
.action-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
    margin-top: 1.5rem;
    animation: fadeUp .5s .5s both;
}
@media(max-width:460px){ .action-grid { grid-template-columns: 1fr; } }
.action-btn {
    display: flex; align-items: center; justify-content: center; gap: .5rem;
    padding: .9rem 1rem; border-radius: 14px; font-weight: 700; font-size: .95rem;
    text-decoration: none; transition: all .2s;
}
.action-btn.primary {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff; box-shadow: 0 4px 16px rgba(79,70,229,.3);
}
.action-btn.primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,.4); }
.action-btn.secondary {
    background: #f8fafc; color: #4f46e5;
    border: 2px solid #e2e8f0;
}
.action-btn.secondary:hover { border-color: #4f46e5; background: #eff6ff; }

.confetti-note {
    background: linear-gradient(135deg, #fefce8, #fef3c7);
    border: 1px solid #fde68a;
    border-radius: 14px; padding: 1rem 1.5rem;
    font-size: .9rem; color: #92400e;
    margin-bottom: 1.5rem; font-weight: 600;
    animation: fadeUp .5s .45s both;
}
</style>

<div class="success-wrap">

    <div class="success-icon">✓</div>

    <h1 class="success-title">Order Placed! 🎉</h1>
    <p class="success-sub">
        Thank you, <strong>{{ session('order.name', 'Customer') }}</strong>!<br>
        Your order has been confirmed and will be delivered soon.
    </p>

    <div class="confetti-note">
        📞 Our team will call you at <strong>{{ session('order.phone', 'your number') }}</strong> to confirm the delivery.
    </div>

    {{-- Order Details --}}
    <div class="order-card">
        <h3>🧾 Order Summary</h3>
        <div class="detail-row">
            <span class="label">Order ID</span>
            <span class="value purple">#EHF-{{ session('order.id', strtoupper(Str::random(8))) }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Medicine</span>
            <span class="value">{{ session('order.medicine', '—') }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Quantity</span>
            <span class="value">{{ session('order.qty', 1) }} unit(s)</span>
        </div>
        <div class="detail-row">
            <span class="label">Unit Price</span>
            <span class="value">৳ {{ session('order.unit_price', '0.00') }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Delivery Charge</span>
            <span class="value">৳ 60.00</span>
        </div>
        <div class="detail-row">
            <span class="label">Total Amount</span>
            <span class="value green">৳ {{ session('order.total', '60.00') }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Payment Method</span>
            <span class="value">{{ ucfirst(session('order.payment', 'Cash on Delivery')) }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Delivery Address</span>
            <span class="value" style="text-align:right;max-width:280px;">{{ session('order.address', '—') }}, {{ session('order.city', '') }}, {{ session('order.district', '') }}</span>
        </div>
    </div>

    {{-- Status Timeline --}}
    <div class="order-card">
        <h3>📍 Order Status</h3>
        <ul class="timeline">
            <li>
                <div class="tl-dot done">✓</div>
                <div class="tl-info">
                    <strong>Order Confirmed</strong>
                    <span>{{ now()->format('d M Y, h:i A') }}</span>
                </div>
            </li>
            <li>
                <div class="tl-dot active">⚡</div>
                <div class="tl-info">
                    <strong>Processing</strong>
                    <span>Your order is being prepared</span>
                </div>
            </li>
            <li>
                <div class="tl-dot wait">🚚</div>
                <div class="tl-info">
                    <strong>Out for Delivery</strong>
                    <span>Expected within 1–3 business days</span>
                </div>
            </li>
            <li>
                <div class="tl-dot wait">🏠</div>
                <div class="tl-info">
                    <strong>Delivered</strong>
                    <span>Pay on delivery</span>
                </div>
            </li>
        </ul>
    </div>

    {{-- Action Buttons --}}
    <div class="action-grid">
        <a href="{{ route('medicines.index') }}" class="action-btn primary">
            💊 Browse More Medicines
        </a>
        <a href="{{ route('home') }}" class="action-btn secondary">
            🏠 Back to Home
        </a>
    </div>

    <p style="font-size:.82rem;color:#94a3b8;margin-top:1.5rem;">
        Questions? Email us at
        <a href="mailto:contact@ehealthfinder.com" style="color:#4f46e5;font-weight:600;">contact@ehealthfinder.com</a>
        &nbsp;·&nbsp;
        <a href="{{ route('refund') }}" style="color:#4f46e5;font-weight:600;">Return Policy</a>
    </p>
</div>
@endsection
