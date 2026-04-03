@if(isset($faqs) && count($faqs) > 0)
<style>
.custom-faq-wrapper {
    margin: 3rem 0;
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem 2.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9;
}
.custom-faq-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}
.custom-faq-item {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 1rem;
    overflow: hidden;
    transition: all 0.3s ease;
}
.custom-faq-item.active {
    border-color: #4f46e5;
    box-shadow: 0 4px 15px rgba(79,70,229,0.1);
}
.custom-faq-btn {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: transparent;
    border: none;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1e293b;
    cursor: pointer;
    text-align: left;
    transition: background 0.2s;
}
.custom-faq-btn:hover {
    background: #f8fafc;
}
.custom-faq-icon {
    font-size: 1.2rem;
    transition: transform 0.3s ease;
    color: #64748b;
}
.custom-faq-item.active .custom-faq-icon {
    transform: rotate(180deg);
    color: #4f46e5;
}
.custom-faq-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, padding 0.4s ease;
    background: #f8fafc;
}
.custom-faq-content-inner {
    padding: 0 1.5rem 1.5rem;
    color: #475569;
    font-size: 1.05rem;
    line-height: 1.7;
}
</style>

<div class="custom-faq-wrapper">
    <h3 class="custom-faq-title">
        <span style="font-size:1.8rem; color: #f59e0b;">💡</span> Frequently Asked Questions
    </h3>
    
    <div class="custom-faq-list">
        @foreach($faqs as $index => $faq)
        <div class="custom-faq-item">
            <button class="custom-faq-btn" onclick="toggleFaq(this)">
                <span>{{ $faq['q'] }}</span>
                <span class="custom-faq-icon">▼</span>
            </button>
            <div class="custom-faq-content">
                <div class="custom-faq-content-inner">
                    {{ $faq['a'] }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
function toggleFaq(btn) {
    const item = btn.parentElement;
    const content = item.querySelector('.custom-faq-content');
    
    // Close other items
    document.querySelectorAll('.custom-faq-item').forEach(otherItem => {
        if (otherItem !== item && otherItem.classList.contains('active')) {
            otherItem.classList.remove('active');
            otherItem.querySelector('.custom-faq-content').style.maxHeight = '0px';
            otherItem.querySelector('.custom-faq-content').style.paddingTop = '0px';
        }
    });

    // Toggle current
    item.classList.toggle('active');
    if (item.classList.contains('active')) {
        content.style.paddingTop = '1rem';
        content.style.maxHeight = content.scrollHeight + 30 + "px";
    } else {
        content.style.maxHeight = '0px';
        content.style.paddingTop = '0px';
    }
}
</script>
@endif
