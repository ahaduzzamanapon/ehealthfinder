@extends('layouts.app')

@section('title', 'Buy ' . $brand->name . ' | eHealthFinder Checkout')
@section('meta_description', 'Order ' . $brand->name . ' safely through eHealthFinder. Fast delivery across Bangladesh.')
@section('canonical', route('checkout.buy', ['id' => $brand->id, 'slug' => $brand->slug]))

@section('content')
<style>
* { box-sizing: border-box; }
.checkout-wrap {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1.25rem 4rem;
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 2rem;
    align-items: start;
}
@media(max-width: 768px) { .checkout-wrap { grid-template-columns: 1fr; } }

/* ─── Left: Form ─────────────────────────────── */
.checkout-form-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 2rem 2.5rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
}
.checkout-form-card h2 {
    font-size: 1.4rem; font-weight: 800; color: #1e1b4b;
    margin: 0 0 1.75rem; display: flex; align-items: center; gap: .6rem;
}
.form-section-title {
    font-size: 0.8rem; font-weight: 800; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 1px;
    margin: 1.75rem 0 1rem; padding-bottom: .5rem;
    border-bottom: 1px solid #f1f5f9;
}
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width: 520px){ .form-row { grid-template-columns: 1fr; } }
.form-group { display: flex; flex-direction: column; gap: .4rem; margin-bottom: 1rem; }
.form-group label { font-size: .9rem; font-weight: 600; color: #374151; }
.form-group input, .form-group select, .form-group textarea {
    padding: .75rem 1rem; border: 1.5px solid #e2e8f0;
    border-radius: 10px; font-size: .95rem; color: #1e293b;
    transition: border-color .2s, box-shadow .2s; background: #f8fafc;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none; border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,.12); background: #fff;
}

/* Payment tabs */
.payment-tabs { display: flex; gap: .6rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
.pay-tab {
    padding: .6rem 1.1rem; border: 2px solid #e2e8f0;
    border-radius: 10px; cursor: pointer; font-weight: 700;
    font-size: .9rem; color: #64748b; transition: all .2s; background: #f8fafc;
    display: flex; align-items: center; gap: .4rem;
}
.pay-tab.active { border-color: #4f46e5; color: #4f46e5; background: #eff6ff; }
.pay-panel { display: none; }
.pay-panel.active { display: block; }
.cod-note {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px;
    padding: 1rem 1.25rem; color: #166534; font-size: .92rem; font-weight: 600;
}

/* ─── Right: Order Summary ───────────────────── */
.order-summary {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 20px; padding: 1.75rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    position: sticky; top: 110px;
}
.order-summary h3 { font-size: 1.1rem; font-weight: 800; color: #1e1b4b; margin: 0 0 1.25rem; }
.product-row {
    display: flex; gap: 1rem; align-items: flex-start;
    padding-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; margin-bottom: 1.25rem;
}
.product-img {
    width: 72px; height: 72px; border-radius: 12px; object-fit: contain;
    background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px; flex-shrink: 0;
}
.product-name { font-weight: 800; color: #1e293b; font-size: .98rem; margin-bottom: .25rem; }
.product-meta { font-size: .82rem; color: #64748b; }
.qty-ctrl {
    display: flex; align-items: center; gap: .6rem; margin-top: .6rem;
}
.qty-btn {
    width: 28px; height: 28px; border-radius: 8px; border: 1.5px solid #e2e8f0;
    background: #f8fafc; font-size: 1rem; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
}
.qty-btn:hover { border-color: #4f46e5; color: #4f46e5; }
#qty-display { font-weight: 800; font-size: 1rem; min-width: 20px; text-align: center; }

.price-row { display: flex; justify-content: space-between; font-size: .95rem; color: #475569; margin-bottom: .6rem; }
.price-row.total { font-weight: 800; font-size: 1.1rem; color: #1e293b; padding-top: .75rem; border-top: 1px solid #f1f5f9; margin-top: .25rem; }
.price-row.total span:last-child { color: #10b981; }

.place-order-btn {
    display: flex; align-items: center; justify-content: center; gap: .6rem;
    width: 100%; padding: 1rem; margin-top: 1.25rem;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff; font-weight: 800; font-size: 1.05rem;
    border: none; border-radius: 14px; cursor: pointer;
    box-shadow: 0 6px 24px rgba(79,70,229,.35);
    transition: transform .2s, box-shadow .2s;
    text-decoration: none;
}
.place-order-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(79,70,229,.45); }

.secure-badges {
    display: flex; justify-content: center; gap: 1rem; margin-top: 1rem; flex-wrap: wrap;
}
.badge { font-size: .78rem; color: #94a3b8; display: flex; align-items: center; gap: .3rem; }

/* Steps */
.checkout-steps {
    display: flex; align-items: center; gap: 0; margin-bottom: 2rem; flex-wrap: wrap;
}
.step { display: flex; align-items: center; gap: .5rem; }
.step-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: #4f46e5; color: #fff; font-weight: 800; font-size: .8rem;
    display: flex; align-items: center; justify-content: center;
}
.step-num.done { background: #10b981; }
.step-num.pending { background: #e2e8f0; color: #94a3b8; }
.step-label { font-size: .82rem; font-weight: 700; color: #1e293b; }
.step-label.pending { color: #94a3b8; }
.step-divider { flex: 1; height: 2px; background: #e2e8f0; margin: 0 .5rem; min-width: 30px; }
</style>

{{-- Breadcrumb --}}
<div class="breadcrumb">
    <a href="{{ route('home') }}">Home</a> ›
    <a href="{{ route('medicines.index') }}">Medicines</a> ›
    <a href="{{ route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug]) }}">{{ $brand->name }}</a> ›
    <span>Checkout</span>
</div>

<div style="max-width:1000px;margin:0 auto;padding:0 1.25rem;">
    {{-- Progress Steps --}}
    <div class="checkout-steps">
        <div class="step">
            <div class="step-num done">✓</div>
            <span class="step-label">Product</span>
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <div class="step-num">2</div>
            <span class="step-label">Checkout</span>
        </div>
        <div class="step-divider"></div>
        <div class="step">
            <div class="step-num pending">3</div>
            <span class="step-label pending">Confirmation</span>
        </div>
    </div>
</div>

<div class="checkout-wrap">

    {{-- ── LEFT: Checkout Form ── --}}
    <div class="checkout-form-card">
        <h2>📋 Order Details</h2>

        <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form">
            @csrf
            <input type="hidden" name="medicine_id" value="{{ $brand->id }}">
            <input type="hidden" name="medicine_name" value="{{ $brand->name }}">
            <input type="hidden" name="medicine_price" value="{{ $brand->price }}">
            <input type="hidden" name="quantity" id="qty-hidden" value="1">

            {{-- Contact --}}
            <div class="form-section-title">📞 Contact Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Your full name" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" placeholder="01XXXXXXXXX" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email Address (optional)</label>
                <input type="email" id="email" name="email" placeholder="your@email.com">
            </div>

            {{-- Delivery --}}
            <div class="form-section-title">🏠 Delivery Address</div>
            <div class="form-group">
                <label for="address">Street Address *</label>
                <input type="text" id="address" name="address" placeholder="House no, road, area" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City / Upazila *</label>
                    <input type="text" id="city" name="city" placeholder="Dhaka, Chittagong..." required>
                </div>
                <div class="form-group">
                    <label for="district">District *</label>
                    <select id="district" name="district" required>
                        <option value="">Select District</option>
                        @foreach(['Dhaka','Chittagong','Sylhet','Rajshahi','Khulna','Barisal','Rangpur','Mymensingh','Comilla','Narayanganj','Gazipur','Tangail','Bogura','Jessore','Dinajpur'] as $d)
                        <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="note">Order Note (optional)</label>
                <textarea id="note" name="note" rows="2" placeholder="Any special instructions..."></textarea>
            </div>

            {{-- Payment --}}
            <div class="form-section-title">💳 Payment Method</div>
            <div class="payment-tabs">
                <div class="pay-tab active" onclick="selectPayment('cod', this)">💵 Cash on Delivery</div>
            </div>
            <input type="hidden" name="payment_method" id="payment_method" value="cod">

            <div id="panel-cod" class="pay-panel active">
                <div class="cod-note">✅ Pay when your medicine is delivered at your door. No advance payment needed.</div>
            </div>
        </form>
    </div>

    {{-- ── RIGHT: Order Summary ── --}}
    <div class="order-summary">
        <h3>🛍️ Order Summary</h3>

        <div class="product-row">
            @php
                $safeImg = str_replace('\\', '/', $brand->image_path ?? '');
                $imgSrc  = $safeImg ? (Str::startsWith($safeImg,'http') ? $safeImg : asset($safeImg)) : null;
            @endphp
            @if($imgSrc)
                <img src="{{ $imgSrc }}" alt="{{ $brand->name }}" class="product-img" onerror="this.outerHTML='<div style=\'width:72px;height:72px;background:#f8fafc;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:2rem;\'>💊</div>'">
            @else
                <div style="width:72px;height:72px;background:#f8fafc;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:2rem;border:1px solid #e2e8f0;">💊</div>
            @endif
            <div style="flex:1;">
                <div class="product-name">{{ $brand->name }}</div>
                <div class="product-meta">{{ $brand->dosage_form }} · {{ $brand->company }}</div>
                @if($brand->strength)
                    <div class="product-meta">{{ $brand->strength }}</div>
                @endif
                <div class="qty-ctrl">
                    <button type="button" class="qty-btn" onclick="changeQty(-1)">−</button>
                    <span id="qty-display">1</span>
                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                </div>
            </div>
        </div>

        @php
            preg_match('/(\d+(\.\d+)?)/', str_replace(',','', $brand->price ?? '0'), $pm);
            $unitPrice = isset($pm[1]) ? (float)$pm[1] : 0;
        @endphp

        <div class="price-row">
            <span>Unit Price</span>
            <span>৳ <span id="unit-price">{{ number_format($unitPrice, 2) }}</span></span>
        </div>
        <div class="price-row">
            <span>Quantity</span>
            <span id="summary-qty">1</span>
        </div>
        <div class="price-row">
            <span>Subtotal</span>
            <span>৳ <span id="subtotal">{{ number_format($unitPrice, 2) }}</span></span>
        </div>
        <div class="price-row">
            <span>Delivery Charge</span>
            <span>৳ 60.00</span>
        </div>
        <div class="price-row total">
            <span>Total</span>
            <span>৳ <span id="total-price">{{ number_format($unitPrice + 60, 2) }}</span></span>
        </div>

        <button type="submit" form="checkout-form" class="place-order-btn">
            🛒 Place Order
        </button>

        <div class="secure-badges">
            <span class="badge">🔒 Secure</span>
            <span class="badge">✅ Verified</span>
            <span class="badge">↩️ <a href="{{ route('refund') }}" style="color:inherit;">Return Policy</a></span>
        </div>

        <p style="font-size:.78rem;color:#cbd5e1;text-align:center;margin-top:.75rem;">
            Estimated delivery: 1–3 business days
        </p>
    </div>
</div>

<script>
    const unitPrice = {{ $unitPrice ?? 0 }};
    let qty = 1;

    function changeQty(delta) {
        qty = Math.max(1, Math.min(99, qty + delta));
        document.getElementById('qty-display').textContent  = qty;
        document.getElementById('summary-qty').textContent  = qty;
        document.getElementById('qty-hidden').value         = qty;
        document.getElementById('subtotal').textContent     = (unitPrice * qty).toFixed(2);
        document.getElementById('total-price').textContent  = (unitPrice * qty + 60).toFixed(2);
    }
</script>
@endsection
