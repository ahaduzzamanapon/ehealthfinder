@extends('admin.layouts.app')
@section('title', 'Medicines')
@section('page-title', '💊 Medicine Management')

@section('content')

{{-- Tabs --}}
<div style="display:flex; gap:0.5rem; margin-bottom:1.5rem;">
    <button onclick="showTab('brands')" id="tab-brands" class="btn btn-primary">💊 Brands ({{ $medicines->total() }})</button>
    <button onclick="showTab('generics')" id="tab-generics" class="btn btn-outline">🧬 Generics ({{ $generics->count() }})</button>
    <button onclick="showTab('add')" id="tab-add" class="btn btn-outline">➕ Add Brand</button>
    <button onclick="showTab('import')" id="tab-import" class="btn btn-outline" style="margin-left:auto; border-color:#8b5cf6; color:#8b5cf6;">📂 Import JSON</button>
</div>

{{-- ── TAB: BRANDS LIST ── --}}
<div id="panel-brands">
    <div class="search-bar">
        <form method="GET" style="display:flex; gap:0.75rem; flex:1;">
            <input type="text" name="q" value="{{ $q }}" class="form-control" style="max-width:320px" placeholder="Search brand name or company...">
            <button class="btn btn-primary">🔍 Search</button>
            @if($q)<a href="{{ route('admin.medicines.index') }}" class="btn btn-outline">✕</a>@endif
        </form>
    </div>
    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Brand Name</th>
                    <th>Generic</th>
                    <th>Company</th>
                    <th>Form</th>
                    <th>Price</th>
                    <th>Antibiotic</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($medicines as $m)
                <tr>
                    <td style="font-weight:600">{{ $m->name }}</td>
                    <td><span class="badge badge-primary">{{ $m->generic?->name ?? '—' }}</span></td>
                    <td style="font-size:0.85rem; color:#94a3b8">{{ $m->company ?? '—' }}</td>
                    <td><span class="badge badge-warning">{{ $m->dosage_form ?? '—' }}</span></td>
                    <td style="color:#34d399; font-weight:600">{{ $m->price ?? '—' }}</td>
                    <td>
                        @if($m->is_antibiotic)
                            <span class="badge badge-red">⚠️ Yes</span>
                        @else
                            <span style="color:#475569; font-size:0.8rem">No</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <a href="{{ route('admin.medicines.edit', $m) }}" class="btn btn-outline btn-sm">✏️</a>
                            <form method="POST" action="{{ route('admin.medicines.destroy', $m) }}" onsubmit="return confirm('Delete {{ $m->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; padding:2rem; color:#64748b;">No medicines found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrap">{{ $medicines->links() }}</div>
    </div>
</div>

{{-- ── TAB: GENERICS ── --}}
<div id="panel-generics" style="display:none;">
    <div style="display:grid; grid-template-columns:1fr 320px; gap:1.5rem;">
        <div class="admin-card" style="margin-bottom:0">
            <div class="admin-card-header">
                <h3>All Generic Names <span class="badge badge-primary">{{ $generics->count() }}</span></h3>
            </div>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Generic Name</th><th>Brands</th></tr></thead>
                <tbody>
                    @foreach($generics as $g)
                    <tr>
                        <td style="color:#64748b; font-size:0.8rem">#{{ $g->id }}</td>
                        <td style="font-weight:600">{{ $g->name }}</td>
                        <td><span class="badge badge-primary">{{ $g->brands_count ?? '—' }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="admin-card" style="margin-bottom:0">
            <h3 style="margin-bottom:1.25rem; font-size:1rem;">➕ Add Generic</h3>
            <form method="POST" action="{{ route('admin.generics.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Generic Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Paracetamol" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">💾 Save</button>
            </form>
        </div>
    </div>
</div>

{{-- ── TAB: ADD BRAND ── --}}
<div id="panel-add" style="display:none; max-width:700px;">
    <div class="admin-card">
        <h3 style="margin-bottom:1.5rem;">➕ Add New Brand</h3>
        <form method="POST" action="{{ route('admin.medicines.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Brand Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. Napa">
                </div>
                <div class="form-group">
                    <label class="form-label">Dosage Form</label>
                    <input type="text" name="dosage_form" class="form-control" placeholder="Tablet, Syrup, Injection...">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Generic Name</label>
                    <select name="generic_id" class="form-control">
                        <option value="">— Select Generic —</option>
                        @foreach($generics as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Company / Manufacturer</label>
                    <input type="text" name="company" class="form-control" placeholder="e.g. Square Pharmaceuticals">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <input type="text" name="price" class="form-control" placeholder="৳ 10.00">
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:0.75rem; padding-top:1.5rem;">
                    <input type="hidden" name="is_antibiotic" value="0">
                    <input type="checkbox" name="is_antibiotic" value="1" id="is_antibiotic" style="width:18px; height:18px; accent-color:#4f46e5;">
                    <label for="is_antibiotic" class="form-label" style="margin:0; cursor:pointer;">⚠️ Is Antibiotic</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">💾 Add Medicine</button>
        </form>
    </div>
</div>

{{-- ── TAB: IMPORT JSON ── --}}
<div id="panel-import" style="display:none; max-width:600px;">
    <div class="admin-card">
        <h3 style="margin-bottom:0.5rem;">📂 Import Database JSON</h3>
        <p style="color:#64748b; font-size:0.9rem; margin-bottom:1.5rem;">Upload a phpMyAdmin exported JSON file containing `brands` (b) or `generics` (g) data to bulk import.</p>
        
        <form method="POST" action="{{ route('admin.medicines.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group" style="padding: 2rem; border:2px dashed #cbd5e1; border-radius:0.5rem; text-align:center; background:#f8fafc; margin-bottom:1.5rem;">
                <input type="file" name="json_file" accept=".json" required id="json_file" style="display:block; margin: 0 auto;">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; background:#8b5cf6; border-color:#8b5cf6;">🚀 Process Import</button>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function showTab(name) {
    ['brands','generics','add', 'import'].forEach(t => {
        document.getElementById('panel-'+t).style.display = (t===name) ? 'block' : 'none';
        
        let tab = document.getElementById('tab-'+t);
        if(t===name) {
            tab.className = 'btn btn-primary';
            if(t==='import') {
                tab.style.background = '#8b5cf6';
                tab.style.borderColor = '#8b5cf6';
                tab.style.color = '#fff';
            } else {
                tab.style.background = '';
                tab.style.borderColor = '';
                tab.style.color = '';
            }
        } else {
            tab.className = 'btn btn-outline';
            if(t==='import') {
                tab.style.background = 'transparent';
                tab.style.borderColor = '#8b5cf6';
                tab.style.color = '#8b5cf6';
            } else {
                tab.style.background = '';
                tab.style.borderColor = '';
                tab.style.color = '';
            }
        }
    });
}
// Show correct tab on page load (based on URL hash or default)
const hash = window.location.hash.replace('#','') || 'brands';
if (['brands','generics','add','import'].includes(hash)) showTab(hash);
</script>
@endsection
