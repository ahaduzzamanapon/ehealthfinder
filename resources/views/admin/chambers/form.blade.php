@extends('admin.layouts.app')
@section('title', isset($chamber) ? 'Edit Chamber' : 'Add Chamber')
@section('page-title', isset($chamber) ? '✏️ Edit Chamber' : '➕ Add Chamber')

@section('content')
<div style="max-width:700px;">
    <div class="admin-card">
        <div style="margin-bottom:1rem; color:#94a3b8; font-size:0.88rem;">
            Doctor: <strong style="color:#f1f5f9;">{{ $doctor->name }}</strong>
        </div>

        <form method="POST" action="{{ isset($chamber) ? route('admin.chambers.update', $chamber) : route('admin.chambers.store') }}">
            @csrf
            @if(isset($chamber)) @method('PUT') @endif
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">

            <div class="form-group">
                <label class="form-label">Hospital</label>
                <select name="hospital_id" class="form-control">
                    <option value="">— Select Hospital —</option>
                    @foreach($hospitals as $h)
                    <option value="{{ $h->id }}" {{ old('hospital_id', $chamber->hospital_id ?? '') == $h->id ? 'selected' : '' }}>
                        {{ $h->name }} @if($h->location) ({{ $h->location->name }}) @endif
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Chamber Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $chamber->address ?? '') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Visiting Hours</label>
                    <input type="text" name="visiting_hour" class="form-control"
                        value="{{ old('visiting_hour', $chamber->visiting_hour ?? '') }}"
                        placeholder="Sat-Thu: 5PM-8PM">
                </div>
                <div class="form-group">
                    <label class="form-label">Appointment Number</label>
                    <input type="text" name="appointment_number" class="form-control"
                        value="{{ old('appointment_number', $chamber->appointment_number ?? '') }}"
                        placeholder="+880...">
                </div>
            </div>

            <div style="display:flex; gap:1rem; margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">💾 Save Chamber</button>
                <a href="{{ route('admin.doctors.edit', $doctor) }}" class="btn btn-outline">← Back to Doctor</a>
            </div>
        </form>
    </div>
</div>
@endsection
