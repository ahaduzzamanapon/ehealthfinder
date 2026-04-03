@php
    $typeMap = [
        App\Models\Brand::class => 'Brand',
        App\Models\Doctor::class => 'Doctor',
        App\Models\BlogPost::class => 'BlogPost'
    ];
    $modelType = $typeMap[get_class($model)] ?? get_class($model);
@endphp

<style>
.custom-rev-wrapper {
    margin: 3rem 0;
    display: flex;
    gap: 2rem;
    align-items: stretch;
}
@media(max-width: 768px) {
    .custom-rev-wrapper { flex-direction: column; }
}
.custom-rev-summary {
    flex: 1;
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.custom-rev-form-container {
    flex: 2;
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9;
}
.custom-rev-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.5rem;
}
.custom-rev-avg {
    font-size: 4rem;
    font-weight: 800;
    color: #10b981;
    line-height: 1;
    margin: 1rem 0;
}
.custom-rev-stars {
    font-size: 2rem;
    letter-spacing: 4px;
    color: #fbbf24;
    margin-bottom: 0.5rem;
}
.custom-rev-label {
    display: block;
    font-size: 0.85rem;
    text-transform: uppercase;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 0.5rem;
    margin-top: 1.2rem;
}
.custom-rev-input {
    width: 100%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.8rem 1rem;
    font-size: 1rem;
    color: #1e293b;
    transition: all 0.2s;
    box-sizing: border-box;
    font-family: inherit;
}
.custom-rev-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
}
.custom-rev-btn {
    background: #4f46e5;
    color: white;
    border: none;
    padding: 0.8rem 2rem;
    border-radius: 50px;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 1.5rem;
    display: inline-block;
}
.custom-rev-btn:hover {
    background: #4338ca;
}
.custom-rev-alert {
    padding: 1rem;
    border-radius: 12px;
    font-weight: 700;
    margin-bottom: 1.5rem;
}
.custom-rev-alert-success { background: #dcfce7; color: #166534; }
.custom-rev-alert-error { background: #fee2e2; color: #991b1b; }
.custom-rev-row {
    display: flex; gap: 1rem;
}
@media(max-width: 600px) {
    .custom-rev-row { flex-direction: column; }
}
.custom-rev-col { flex: 1; }
</style>

<div class="custom-rev-wrapper" id="reviews">
    <!-- Review Summary -->
    <div class="custom-rev-summary">
        <h3 class="custom-rev-title">User Reviews</h3>
        @if($model->reviewCount > 0)
            <div class="custom-rev-avg">{{ number_format($model->averageRating, 1) }}</div>
            <div class="custom-rev-stars">
                @for($i=1; $i<=5; $i++)
                    {!! $i <= round($model->averageRating) ? '&#9733;' : '&#9734;' !!}
                @endfor
            </div>
            <p style="color: #64748b; font-weight: 600; margin: 0;">Based on {{ $model->reviewCount }} reviews</p>
        @else
            <div style="margin: 2rem 0;">
                <span style="font-size:3.5rem; display:block; margin-bottom:1rem; opacity:0.5;">⭐</span>
                <div style="font-weight: 800; color: #1e293b; font-size: 1.1rem; margin-bottom: 0.3rem;">No reviews yet!</div>
                <div style="color: #64748b;">Be the first to share your experience.</div>
            </div>
        @endif
    </div>
    
    <!-- Review Form -->
    <div class="custom-rev-form-container">
        <h4 style="font-size: 1.4rem; font-weight: 800; color: #1e3a8a; margin-top: 0; margin-bottom: 1.5rem;">Write a Review</h4>
        
        @if(session('success'))
            <div class="custom-rev-alert custom-rev-alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="custom-rev-alert custom-rev-alert-error">❌ {{ session('error') }}</div>
        @endif
        
        <form action="{{ route('reviews.store') }}" method="POST">
            @csrf
            <input type="hidden" name="reviewable_type" value="{{ $modelType }}">
            <input type="hidden" name="reviewable_id" value="{{ $model->id }}">
            
            <div class="custom-rev-row">
                <div class="custom-rev-col">
                    <label class="custom-rev-label">Your Name <span style="color:#ef4444">*</span></label>
                    <input type="text" name="author_name" class="custom-rev-input" required placeholder="John Doe">
                </div>
                <div class="custom-rev-col">
                    <label class="custom-rev-label">Email (Optional)</label>
                    <input type="email" name="author_email" class="custom-rev-input" placeholder="john@example.com">
                </div>
            </div>
            
            <div>
                <label class="custom-rev-label">Rating <span style="color:#ef4444">*</span></label>
                <select name="rating" class="custom-rev-input" style="font-weight: 700; color: #b45309;" required>
                    <option value="5" selected>⭐⭐⭐⭐⭐ - Excellent</option>
                    <option value="4">⭐⭐⭐⭐ - Good</option>
                    <option value="3">⭐⭐⭐ - Average</option>
                    <option value="2">⭐⭐ - Poor</option>
                    <option value="1">⭐ - Terrible</option>
                </select>
            </div>
            
            <div>
                <label class="custom-rev-label">Your Experience <span style="color:#ef4444">*</span></label>
                <textarea name="body" class="custom-rev-input" rows="4" required placeholder="Write your feedback here..."></textarea>
            </div>
            
            <div style="text-align: right;">
                <button type="submit" class="custom-rev-btn">Post Review &rarr;</button>
            </div>
        </form>
    </div>
</div>
