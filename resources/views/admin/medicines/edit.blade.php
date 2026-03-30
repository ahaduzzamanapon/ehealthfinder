@extends('admin.layouts.app')
@section('title', 'Edit Medicine')
@section('page-title', "✏️ Edit: {$medicine->name}")

@section('content')
<div style="max-width:700px;">
    <div class="admin-card">
        <form method="POST" action="{{ route('admin.medicines.update', $medicine) }}">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Brand Name *</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $medicine->name) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Dosage Form</label>
                    <input type="text" name="dosage_form" class="form-control" value="{{ old('dosage_form', $medicine->dosage_form) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Generic Name</label>
                    <select name="generic_id" class="form-control">
                        <option value="">— Select Generic —</option>
                        @foreach($generics as $g)
                        <option value="{{ $g->id }}" {{ $medicine->generic_id == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Company / Manufacturer</label>
                    <input type="text" name="company" class="form-control" value="{{ old('company', $medicine->company) }}">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Price</label>
                    <input type="text" name="price" class="form-control" value="{{ old('price', $medicine->price) }}">
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:0.75rem; padding-top:1.5rem;">
                    <input type="hidden" name="is_antibiotic" value="0">
                    <input type="checkbox" name="is_antibiotic" value="1" id="is_antibiotic"
                        {{ $medicine->is_antibiotic ? 'checked' : '' }}
                        style="width:18px; height:18px; accent-color:#4f46e5;">
                    <label for="is_antibiotic" class="form-label" style="margin:0; cursor:pointer;">⚠️ Is Antibiotic</label>
                </div>
            </div>
            <div style="display:flex; gap:1rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">💾 Update Medicine</button>
                <a href="{{ route('admin.medicines.index') }}" class="btn btn-outline">← Back</a>
            </div>
        </form>
    </div>
</div>
@endsection
