<!-- Autocomplete component - include in any layout -->
<style>
.autocomplete-wrap { position: relative; }
.autocomplete-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0; right: 0;
    background: white;
    border-radius: 14px;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 20px 40px -8px rgba(0,0,0,0.15);
    z-index: 9999;
    overflow: hidden;
    display: none;
    max-height: 320px;
    overflow-y: auto;
}
.autocomplete-dropdown.open { display: block; animation: fadeIn 0.15s ease; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }

.ac-item {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 0.8rem 1.2rem;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #f1f5f9;
}
.ac-item:last-child { border-bottom: none; }
.ac-item:hover, .ac-item.focused { background: #f8faff; }

.ac-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.ac-icon.doctor  { background: #eff6ff; }
.ac-icon.medicine{ background: #f0fdf4; }
.ac-icon.generic { background: #fefce8; }

.ac-text { flex: 1; min-width: 0; }
.ac-label { font-size: 0.93rem; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ac-sub   { font-size: 0.78rem; color: #94a3b8; margin-top: 0.1rem; }

.ac-badge {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.15rem 0.5rem;
    border-radius: 50px;
}
.ac-badge.doctor   { background: #dbeafe; color: #1e40af; }
.ac-badge.medicine { background: #dcfce7; color: #166534; }
.ac-badge.generic  { background: #fef9c3; color: #854d0e; }

.ac-no-result { padding: 1.2rem; text-align: center; color: #94a3b8; font-size: 0.9rem; }
</style>

<script>
function initAutocomplete(inputEl, dropdownEl, apiUrl, extraParams) {
    let debounceTimer = null;
    let focused = -1;
    let items = [];

    inputEl.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        focused = -1;
        const q = this.value.trim();
        if (q.length < 2) { dropdownEl.classList.remove('open'); return; }

        debounceTimer = setTimeout(() => {
            const params = new URLSearchParams({ q, ...(extraParams || {}) });
            fetch(apiUrl + '?' + params)
                .then(r => r.json())
                .then(data => {
                    items = data;
                    renderDropdown(data, inputEl, dropdownEl);
                });
        }, 250);
    });

    inputEl.addEventListener('keydown', function(e) {
        const els = dropdownEl.querySelectorAll('.ac-item');
        if (e.key === 'ArrowDown') { e.preventDefault(); focused = Math.min(focused + 1, els.length - 1); highlight(els); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); focused = Math.max(focused - 1, -1); highlight(els); }
        else if (e.key === 'Enter' && focused >= 0) { e.preventDefault(); els[focused]?.click(); }
        else if (e.key === 'Escape') { dropdownEl.classList.remove('open'); }
    });

    function highlight(els) {
        els.forEach((el, i) => el.classList.toggle('focused', i === focused));
        if (focused >= 0) els[focused]?.scrollIntoView({ block: 'nearest' });
    }

    document.addEventListener('click', (e) => {
        if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
            dropdownEl.classList.remove('open');
        }
    });
}

function renderDropdown(data, inputEl, dropdownEl) {
    if (!data.length) {
        dropdownEl.innerHTML = '<div class="ac-no-result">No results found</div>';
        dropdownEl.classList.add('open');
        return;
    }

    const icons = { doctor: '👨‍⚕️', medicine: '💊', generic: '🧬' };
    const labels = { doctor: 'Doctor', medicine: 'Medicine', generic: 'Generic' };

    dropdownEl.innerHTML = data.map(item => `
        <a class="ac-item" href="${item.url}" data-label="${item.label}">
            <div class="ac-icon ${item.type}">${icons[item.type] || '🔍'}</div>
            <div class="ac-text">
                <div class="ac-label">${highlight_match(item.label, inputEl.value)}</div>
                <div class="ac-sub">${item.sub || ''}</div>
            </div>
            <span class="ac-badge ${item.type}">${labels[item.type] || item.type}</span>
        </a>
    `).join('');
    dropdownEl.classList.add('open');
}

function highlight_match(text, query) {
    if (!query) return text;
    const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
    return text.replace(re, '<mark style="background:rgba(79,70,229,0.12);color:#4f46e5;border-radius:3px;padding:0 2px;">$1</mark>');
}
</script>
