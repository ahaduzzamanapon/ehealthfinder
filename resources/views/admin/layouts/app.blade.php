<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'Admin') — eHealthFinder Admin</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
:root {
    --sidebar-w: 260px;
    --primary: #4f46e5;
    --primary-dark: #3730a3;
    --primary-light: #818cf8;
    --bg: #0f172a;
    --sidebar-bg: #1e293b;
    --card-bg: #1e293b;
    --border: #334155;
    --text: #f1f5f9;
    --text-muted: #94a3b8;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --info: #3b82f6;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

/* ── Sidebar ── */
.sidebar {
    width: var(--sidebar-w);
    background: var(--sidebar-bg);
    height: 100vh;
    position: fixed;
    top: 0; left: 0;
    display: flex;
    flex-direction: column;
    border-right: 1px solid var(--border);
    z-index: 100;
    overflow-y: auto;
}
.sidebar-logo {
    padding: 1.5rem 1.25rem 1rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.sidebar-logo-icon {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
}
.sidebar-logo h2 { font-size: 1rem; font-weight: 700; color: var(--text); }
.sidebar-logo small { font-size: 0.7rem; color: var(--text-muted); display: block; }

.sidebar-nav { padding: 1rem 0; flex: 1; }
.nav-section-title {
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--text-muted);
    padding: 0.75rem 1.25rem 0.4rem;
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 1.25rem;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    border-radius: 0;
    transition: all 0.15s;
    margin: 1px 0.5rem;
    border-radius: 8px;
}
.nav-item:hover { background: rgba(79,70,229,0.15); color: var(--text); }
.nav-item.active { background: linear-gradient(135deg, rgba(79,70,229,0.3), rgba(129,140,248,0.2)); color: #c7d2fe; border-left: 3px solid var(--primary-light); }
.nav-item .icon { font-size: 1.1rem; width: 20px; text-align: center; }
.nav-badge { margin-left: auto; background: var(--primary); color: white; font-size: 0.65rem; padding: 2px 7px; border-radius: 20px; font-weight: 600; }

.sidebar-footer {
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--border);
}
.sidebar-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
}
.sidebar-user .avatar {
    width: 36px; height: 36px;
    background: var(--primary);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.sidebar-user .user-info small { font-size: 0.75rem; color: var(--text-muted); }
.sidebar-user .user-info strong { font-size: 0.85rem; display: block; }

/* ── Main Content ── */
.main-wrap {
    margin-left: var(--sidebar-w);
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
.topbar {
    background: var(--sidebar-bg);
    border-bottom: 1px solid var(--border);
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
}
.topbar-title { font-size: 1.1rem; font-weight: 700; }
.topbar-actions { display: flex; gap: 0.75rem; align-items: center; }
.topbar-actions a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.85rem;
    padding: 0.4rem 0.9rem;
    border-radius: 8px;
    border: 1px solid var(--border);
    transition: all 0.15s;
}
.topbar-actions a:hover { background: rgba(255,255,255,0.05); color: var(--text); }
.topbar-actions a.danger { color: var(--danger); border-color: var(--danger); }

.page-content { padding: 2rem; flex: 1; }

/* ── Cards ── */
.admin-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.admin-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}
.admin-card-header h3 { font-size: 1rem; font-weight: 700; }

/* ── Stat Cards ── */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 1.75rem; }
.stat-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.3); }
.stat-card::before {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 80px;
    border-radius: 0 0 0 80px;
    opacity: 0.1;
}
.stat-card.blue::before { background: var(--info); }
.stat-card.green::before { background: var(--success); }
.stat-card.purple::before { background: var(--primary); }
.stat-card.yellow::before { background: var(--warning); }
.stat-card.red::before { background: var(--danger); }

.stat-icon { font-size: 2rem; margin-bottom: 0.75rem; }
.stat-value { font-size: 2rem; font-weight: 800; margin-bottom: 0.25rem; }
.stat-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }

/* ── Tables ── */
.admin-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.admin-table th {
    text-align: left;
    padding: 0.75rem 1rem;
    background: rgba(255,255,255,0.04);
    color: var(--text-muted);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--border);
}
.admin-table td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid rgba(51,65,85,0.5);
    vertical-align: middle;
}
.admin-table tr:hover td { background: rgba(255,255,255,0.02); }
.admin-table tr:last-child td { border-bottom: none; }

/* ── Buttons ── */
.btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.15s; }
.btn-primary { background: var(--primary); color: white; }
.btn-primary:hover { background: var(--primary-dark); }
.btn-success { background: var(--success); color: white; }
.btn-danger { background: transparent; color: var(--danger); border: 1px solid var(--danger); }
.btn-danger:hover { background: var(--danger); color: white; }
.btn-sm { padding: 0.3rem 0.7rem; font-size: 0.78rem; }
.btn-outline { background: transparent; color: var(--text); border: 1px solid var(--border); }
.btn-outline:hover { background: rgba(255,255,255,0.05); }

/* ── Forms ── */
.form-group { margin-bottom: 1.25rem; }
.form-label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem; }
.form-control {
    width: 100%;
    padding: 0.65rem 0.9rem;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 0.88rem;
    font-family: 'Inter', sans-serif;
    transition: border-color 0.15s;
}
.form-control:focus { outline: none; border-color: var(--primary); }
select.form-control option { background: #1e293b; color: white; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

/* ── Alerts ── */
.alert { padding: 0.85rem 1.25rem; border-radius: 10px; font-size: 0.88rem; margin-bottom: 1rem; }
.alert-success { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #34d399; }
.alert-danger  { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #f87171; }

/* ── Search bar ── */
.search-bar { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; }
.search-bar .form-control { max-width: 320px; }

/* ── Badge ── */
.badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
.badge-primary { background: rgba(79,70,229,0.25); color: #818cf8; }
.badge-success { background: rgba(16,185,129,0.2); color: #34d399; }
.badge-warning { background: rgba(245,158,11,0.2); color: #fbbf24; }
.badge-red { background: rgba(239,68,68,0.2); color: #f87171; }

/* ── Pagination ── */
.pagination-wrap { margin-top: 1.5rem; }
.pagination-wrap nav { display: flex; }
</style>
@yield('extra-styles')
</head>
<body>

{{-- SIDEBAR --}}
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">🏥</div>
        <div>
            <h2>eHealth Admin</h2>
            <small>Control Panel</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Main</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="icon">📊</span> Dashboard
        </a>

        <div class="nav-section-title">Manage</div>
        <a href="{{ route('admin.doctors.index') }}" class="nav-item {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}">
            <span class="icon">👨‍⚕️</span> Doctors
        </a>
        <a href="{{ route('admin.specialties.index') }}" class="nav-item {{ request()->routeIs('admin.specialties.*') ? 'active' : '' }}">
            <span class="icon">🩺</span> Specialties
        </a>
        <a href="{{ route('admin.locations.index') }}" class="nav-item {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
            <span class="icon">📍</span> Locations
        </a>
        <a href="{{ route('admin.medicines.index') }}" class="nav-item {{ request()->routeIs('admin.medicines.*') ? 'active' : '' }}">
            <span class="icon">💊</span> Medicines
        </a>

        <div class="nav-section-title">Blog / Content</div>
        <a href="{{ route('admin.blog.posts.index') }}" class="nav-item {{ request()->routeIs('admin.blog.posts.*') ? 'active' : '' }}">
            <span class="icon">📝</span> Blog Posts
        </a>
        <a href="{{ route('admin.blog.ai-writer') }}" class="nav-item {{ request()->routeIs('admin.blog.ai-writer*') ? 'active' : '' }}">
            <span class="icon">🤖</span> AI Blog Writer
        </a>
        <a href="{{ route('admin.blog.categories.index') }}" class="nav-item {{ request()->routeIs('admin.blog.categories.*') ? 'active' : '' }}">
            <span class="icon">📂</span> Categories
        </a>

        <div class="nav-section-title">Analytics</div>
        <a href="{{ route('admin.visitors.index') }}" class="nav-item {{ request()->routeIs('admin.visitors.*') ? 'active' : '' }}">
            <span class="icon">📊</span> Visitor Analytics
        </a>

        <div class="nav-section-title">Site</div>
        <a href="{{ url('/') }}" target="_blank" class="nav-item">
            <span class="icon">🌐</span> View Site
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar">👤</div>
            <div class="user-info">
                <strong>Administrator</strong>
                <small>{{ session('admin_email') }}</small>
            </div>
        </div>
    </div>
</aside>

{{-- MAIN CONTENT --}}
<div class="main-wrap">
    <div class="topbar">
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-actions">
            <a href="{{ url('/') }}" target="_blank">🌐 View Site</a>
            <a href="{{ route('admin.logout') }}" class="danger">🚪 Logout</a>
        </div>
    </div>

    <div class="page-content">
        @if(session('success'))
            <div class="alert alert-success">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e) {{ $e }}<br> @endforeach
            </div>
        @endif

        @yield('content')
    </div>
</div>

@yield('scripts')
</body>
</html>
