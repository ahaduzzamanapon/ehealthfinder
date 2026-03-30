@extends('admin.layouts.app')
@section('title', 'Doctors')
@section('page-title', '👨‍⚕️ Doctor Management')

@section('content')

<div class="search-bar">
    <form method="GET" style="display:flex; gap:0.75rem; flex:1; align-items:center;">
        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search by doctor name...">
        <button class="btn btn-primary" type="submit">🔍 Search</button>
        @if($q)<a href="{{ route('admin.doctors.index') }}" class="btn btn-outline">✕ Clear</a>@endif
    </form>
    <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary">+ Add Doctor</a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>All Doctors <span class="badge badge-primary">{{ $doctors->total() }}</span></h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Degrees</th>
                <th>Specialty</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doctors as $doc)
            <tr>
                <td style="color:#64748b; font-size:0.8rem;">#{{ $doc->id }}</td>
                <td>
                    <div style="font-weight:600;">{{ $doc->name }}</div>
                    @if($doc->designation)
                    <div style="font-size:0.75rem; color:#94a3b8;">{{ Str::limit($doc->designation, 50) }}</div>
                    @endif
                </td>
                <td style="font-size:0.8rem; color:#94a3b8; max-width:180px;">{{ Str::limit($doc->degrees, 60) }}</td>
                <td>
                    @if($doc->specialty)
                    <span class="badge badge-primary">{{ $doc->specialty->name }}</span>
                    @else <span style="color:#475569">—</span> @endif
                </td>
                <td>{{ $doc->location?->name ?? '—' }}</td>
                <td>
                    <div style="display:flex; gap:0.5rem; align-items:center;">
                        <a href="{{ route('admin.doctors.edit', $doc) }}" class="btn btn-outline btn-sm">✏️ Edit</a>
                        <a href="{{ route('doctor.show', ['idslug' => $doc->seo_slug]) }}" target="_blank" class="btn btn-outline btn-sm">👁️</a>
                        <form method="POST" action="{{ route('admin.doctors.destroy', $doc) }}" onsubmit="return confirm('Delete {{ $doc->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" type="submit">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center; padding:2rem; color:#64748b;">No doctors found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $doctors->links() }}</div>
</div>
@endsection
