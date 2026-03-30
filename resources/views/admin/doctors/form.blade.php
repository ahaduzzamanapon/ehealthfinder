@extends('admin.layouts.app')
@section('title', isset($doctor) ? 'Edit Doctor' : 'Add Doctor')
@section('page-title', isset($doctor) ? "✏️ Edit: {$doctor->name}" : '➕ Add New Doctor')

@section('content')

<div style="display:flex; gap:1.5rem; align-items:flex-start;">
    {{-- Main Form --}}
    <div style="flex:2;">
        <div class="admin-card">
            <form method="POST" action="{{ isset($doctor) ? route('admin.doctors.update', $doctor) : route('admin.doctors.store') }}">
                @csrf
                @if(isset($doctor)) @method('PUT') @endif

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $doctor->name ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Degrees / Qualifications</label>
                        <input type="text" name="degrees" class="form-control" value="{{ old('degrees', $doctor->degrees ?? '') }}" placeholder="MBBS, MCh, MS...">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Specialty</label>
                        <select name="specialty_id" class="form-control">
                            <option value="">— Select Specialty —</option>
                            @foreach($specialties as $s)
                            <option value="{{ $s->id }}" {{ old('specialty_id', $doctor->specialty_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location / District</label>
                        <select name="location_id" class="form-control">
                            <option value="">— Select Location —</option>
                            @foreach($locations as $l)
                            <option value="{{ $l->id }}" {{ old('location_id', $doctor->location_id ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Designation</label>
                        <input type="text" name="designation" class="form-control" value="{{ old('designation', $doctor->designation ?? '') }}" placeholder="Professor, Associate Professor...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Experience</label>
                        <input type="text" name="experience" class="form-control" value="{{ old('experience', $doctor->experience ?? '') }}" placeholder="15 years...">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Workplace / Hospital</label>
                    <input type="text" name="workplace" class="form-control" value="{{ old('workplace', $doctor->workplace ?? '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Image URL</label>
                    <input type="text" name="image_path" class="form-control" value="{{ old('image_path', $doctor->image_path ?? '') }}" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label class="form-label">About / Bio</label>
                    <textarea name="about_text" class="form-control" rows="4" placeholder="Doctor biography...">{{ old('about_text', $doctor->about_text ?? '') }}</textarea>
                </div>

                <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        💾 {{ isset($doctor) ? 'Update Doctor' : 'Save Doctor' }}
                    </button>
                    <a href="{{ route('admin.doctors.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Chambers sidebar (only on edit) --}}
    @if(isset($doctor))
    <div style="flex:1; min-width:280px;">
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>🏥 Chambers</h3>
                <a href="{{ route('admin.chambers.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-primary btn-sm">+ Add</a>
            </div>
            @php $chambers = $doctor->chambers()->with('hospital')->get(); @endphp
            @forelse($chambers as $ch)
            <div style="padding:0.75rem; background:rgba(255,255,255,0.04); border-radius:10px; margin-bottom:0.75rem; border:1px solid #334155;">
                <div style="font-weight:600; font-size:0.85rem;">{{ $ch->hospital?->name ?? 'Unknown Hospital' }}</div>
                <div style="font-size:0.78rem; color:#94a3b8; margin-top:0.3rem;">📍 {{ $ch->address }}</div>
                <div style="font-size:0.78rem; color:#94a3b8;">⏰ {{ $ch->visiting_hour }}</div>
                <div style="font-size:0.78rem; color:#94a3b8;">📞 {{ $ch->appointment_number }}</div>
                <div style="margin-top:0.5rem; display:flex; gap:0.5rem;">
                    <a href="{{ route('admin.chambers.edit', $ch) }}" class="btn btn-outline btn-sm">Edit</a>
                    <form method="POST" action="{{ route('admin.chambers.destroy', $ch) }}" onsubmit="return confirm('Delete this chamber?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">🗑️</button>
                    </form>
                </div>
            </div>
            @empty
            <p style="color:#64748b; font-size:0.85rem;">No chambers added yet.</p>
            @endforelse
        </div>
    </div>
    @endif
</div>

@endsection
