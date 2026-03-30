@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page-title', '📊 Dashboard Overview')

@section('content')

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon">👨‍⚕️</div>
        <div class="stat-value">{{ number_format($totalDoctors) }}</div>
        <div class="stat-label">Total Doctors</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">🩺</div>
        <div class="stat-value">{{ $totalSpecialties }}</div>
        <div class="stat-label">Specialties</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">📍</div>
        <div class="stat-value">{{ $totalLocations }}</div>
        <div class="stat-label">Locations</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon">💊</div>
        <div class="stat-value">{{ number_format($totalMedicines) }}</div>
        <div class="stat-label">Medicines</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon">👁️</div>
        <div class="stat-value">{{ number_format($visitorsToday) }}</div>
        <div class="stat-label">Visitors Today</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon">📅</div>
        <div class="stat-value">{{ number_format($visitorsWeek) }}</div>
        <div class="stat-label">This Week</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon">📆</div>
        <div class="stat-value">{{ number_format($visitorsYear) }}</div>
        <div class="stat-label">This Year</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon">🌍</div>
        <div class="stat-value">{{ number_format($visitorsTotal) }}</div>
        <div class="stat-label">Total Unique Visitors</div>
    </div>
</div>

{{-- Chart + Top specialties --}}
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem; margin-bottom:1.5rem;">
    {{-- Visitor Chart --}}
    <div class="admin-card" style="margin-bottom:0">
        <div class="admin-card-header">
            <h3>📈 Visitor Trend (Last 7 Days)</h3>
        </div>
        <canvas id="visitorChart" height="80"></canvas>
    </div>

    {{-- Top Specialties --}}
    <div class="admin-card" style="margin-bottom:0">
        <div class="admin-card-header">
            <h3>🏆 Top Specialties</h3>
        </div>
        <table class="admin-table">
            <thead><tr><th>Specialty</th><th>Doctors</th></tr></thead>
            <tbody>
                @foreach($topSpecialties as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td><span class="badge badge-primary">{{ $s->total }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Doctors + Quick Actions --}}
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:1.5rem;">
    <div class="admin-card" style="margin-bottom:0">
        <div class="admin-card-header">
            <h3>🆕 Recently Added Doctors</h3>
            <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary btn-sm">+ Add Doctor</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Location</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentDoctors as $doc)
                <tr>
                    <td style="font-weight:600">{{ $doc->name }}</td>
                    <td><span class="badge badge-primary">{{ $doc->specialty?->name ?? '—' }}</span></td>
                    <td>{{ $doc->location?->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('admin.doctors.edit', $doc) }}" class="btn btn-outline btn-sm">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Quick Actions --}}
    <div class="admin-card" style="margin-bottom:0">
        <div class="admin-card-header">
            <h3>⚡ Quick Actions</h3>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <a href="{{ route('admin.doctors.create') }}" class="btn btn-primary">👨‍⚕️ Add New Doctor</a>
            <a href="{{ route('admin.specialties.index') }}" class="btn btn-outline">🩺 Manage Specialties</a>
            <a href="{{ route('admin.locations.index') }}" class="btn btn-outline">📍 Manage Locations</a>
            <a href="{{ route('admin.medicines.index') }}" class="btn btn-outline">💊 Manage Medicines</a>
            <a href="{{ url('/doctors') }}" target="_blank" class="btn btn-outline">🌐 Doctor Directory</a>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const ctx = document.getElementById('visitorChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Unique Visitors',
            data: {!! json_encode($chartData) !!},
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,0.1)',
            borderWidth: 2.5,
            pointBackgroundColor: '#6366f1',
            pointRadius: 4,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8', precision: 0 }, beginAtZero: true }
        }
    }
});
</script>
@endsection
