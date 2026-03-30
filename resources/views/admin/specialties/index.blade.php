@extends('admin.layouts.app')
@section('title', 'Specialties')
@section('page-title', '🩺 Specialty Management')

@section('content')
<div style="display:grid; grid-template-columns:1fr 320px; gap:1.5rem; align-items:flex-start;">

    {{-- List --}}
    <div class="admin-card" style="margin-bottom:0">
        <div class="admin-card-header">
            <h3>All Specialties <span class="badge badge-primary">{{ $specialties->total() }}</span></h3>
            <form method="GET" style="display:flex; gap:0.5rem;">
                <input type="text" name="q" value="{{ $q }}" class="form-control" style="width:200px" placeholder="Search...">
                <button class="btn btn-outline btn-sm">🔍</button>
            </form>
        </div>
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Specialty Name</th><th>Doctors</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($specialties as $s)
                <tr>
                    <td style="color:#64748b; font-size:0.8rem;">#{{ $s->id }}</td>
                    <td style="font-weight:600">{{ $s->name }}</td>
                    <td><span class="badge badge-primary">{{ $s->doctors_count }}</span></td>
                    <td>
                        <div style="display:flex; gap:0.5rem;">
                            <button onclick="editSpecialty({{ $s->id }}, '{{ addslashes($s->name) }}')" class="btn btn-outline btn-sm">✏️ Edit</button>
                            <form method="POST" action="{{ route('admin.specialties.destroy', $s) }}" onsubmit="return confirm('Delete {{ $s->name }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:2rem; color:#64748b;">No specialties found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="pagination-wrap">{{ $specialties->links() }}</div>
    </div>

    {{-- Add / Edit Form --}}
    <div>
        <div class="admin-card" style="margin-bottom:0" id="form-card">
            <h3 id="form-title" style="margin-bottom:1.25rem; font-size:1rem;">➕ Add Specialty</h3>
            <form id="specialty-form" method="POST" action="{{ route('admin.specialties.store') }}">
                @csrf
                <input type="hidden" id="form-method" name="_method" value="">
                <div class="form-group">
                    <label class="form-label">Specialty Name *</label>
                    <input type="text" name="name" id="specialty-name" class="form-control" placeholder="e.g. Cardiologist" required>
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
function editSpecialty(id, name) {
    document.getElementById('form-title').innerText = '✏️ Edit Specialty';
    document.getElementById('specialty-name').value = name;
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('specialty-form').action = '/admin/specialties/' + id;
    document.getElementById('form-card').scrollIntoView({ behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('form-title').innerText = '➕ Add Specialty';
    document.getElementById('specialty-name').value = '';
    document.getElementById('form-method').value = '';
    document.getElementById('specialty-form').action = '{{ route('admin.specialties.store') }}';
}
</script>
@endsection
