@extends('admin.layouts.app')
@section('title', 'Locations')
@section('page-title', '📍 Location Management')

@section('content')
<div style="display:grid; grid-template-columns:1fr 320px; gap:1.5rem; align-items:flex-start;">

    {{-- List --}}
    <div class="admin-card" style="margin-bottom:0">
        <div class="admin-card-header">
            <h3>All Locations <span class="badge badge-primary">{{ $locations->total() }}</span></h3>
            <form method="GET" style="display:flex; gap:0.5rem;">
                <input type="text" name="q" value="{{ $q }}" class="form-control" style="width:200px" placeholder="Search...">
                <button class="btn btn-outline btn-sm">🔍</button>
            </form>
        </div>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Location Name</th><th>Doctors</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($locations as $loc)
                <tr>
                    <td style="color:#64748b; font-size:0.8rem;">#{{ $loc->id }}</td>
                    <td style="font-weight:600">{{ $loc->name }}</td>
                    <td><span class="badge badge-success">{{ $loc->doctors_count }}</span></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <button onclick="editLocation({{ $loc->id }}, '{{ addslashes($loc->name) }}')" class="btn btn-outline btn-sm">✏️ Edit</button>
                            <form method="POST" action="{{ route('admin.locations.destroy', $loc) }}" onsubmit="return confirm('Delete {{ $loc->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:2rem; color:#64748b;">No locations found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrap">{{ $locations->links() }}</div>
    </div>

    {{-- Add / Edit Form --}}
    <div>
        <div class="admin-card" style="margin-bottom:0" id="form-card">
            <h3 id="form-title" style="margin-bottom:1.25rem; font-size:1rem;">➕ Add Location</h3>
            <form id="location-form" method="POST" action="{{ route('admin.locations.store') }}">
                @csrf
                <input type="hidden" id="form-method" name="_method" value="">
                <div class="form-group">
                    <label class="form-label">Location / District Name *</label>
                    <input type="text" name="name" id="location-name" class="form-control" placeholder="e.g. Dhaka, Chittagong" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">💾 Save</button>
                <button type="button" onclick="resetForm()" class="btn btn-outline" style="width:100%; margin-top:0.5rem;">Cancel</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function editLocation(id, name) {
    document.getElementById('form-title').innerText = '✏️ Edit Location';
    document.getElementById('location-name').value = name;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('location-form').action = '/admin/locations/' + id;
    document.getElementById('form-card').scrollIntoView({ behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('form-title').innerText = '➕ Add Location';
    document.getElementById('location-name').value = '';
    document.getElementById('form-method').value = '';
    document.getElementById('location-form').action = '{{ route('admin.locations.store') }}';
}
</script>
@endsection
