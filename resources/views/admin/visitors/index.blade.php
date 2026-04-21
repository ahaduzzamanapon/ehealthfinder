@extends('admin.layouts.app')

@section('title', 'Visitor Analytics')

@section('content')
<style>
* { box-sizing: border-box; }
.va-wrap { max-width: 1400px; margin: 0 auto; padding: 1.5rem 1.25rem 4rem; }

/* Page Header */
.va-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
.va-header h1 { font-size: 1.6rem; font-weight: 800; color: #1e1b4b; margin: 0; display: flex; align-items: center; gap: .6rem; }
.va-badge { background: #eef2ff; color: #4f46e5; font-size: .75rem; font-weight: 700; padding: .3rem .8rem; border-radius: 99px; }

/* Stat Cards */
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }
.stat-card {
    background: #fff; border-radius: 18px; padding: 1.5rem 1.75rem;
    border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    position: relative; overflow: hidden;
}
.stat-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: var(--color);
}
.stat-card .label { font-size: .78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .8px; margin-bottom: .6rem; }
.stat-card .val   { font-size: 2.2rem; font-weight: 900; color: #1e1b4b; line-height: 1; }
.stat-card .sub   { font-size: .82rem; color: #64748b; margin-top: .4rem; }
.stat-card .icon  { position: absolute; right: 1.25rem; top: 1.25rem; font-size: 1.8rem; opacity: .15; }

/* Chart */
.section-card {
    background: #fff; border-radius: 18px; padding: 1.5rem 1.75rem;
    border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    margin-bottom: 1.5rem;
}
.section-title { font-size: 1rem; font-weight: 800; color: #1e1b4b; margin: 0 0 1.25rem; display: flex; align-items: center; gap: .5rem; }

/* Bar Chart */
.bar-chart { display: flex; align-items: flex-end; gap: 6px; height: 140px; padding-bottom: .5rem; }
.bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; }
.bar-inner { width: 100%; border-radius: 6px 6px 0 0; background: linear-gradient(180deg, #4f46e5, #7c3aed); transition: height .3s; min-height: 2px; position: relative; }
.bar-inner:hover::after { content: attr(data-tip); position: absolute; top: -30px; left: 50%; transform: translateX(-50%); background: #1e1b4b; color: #fff; font-size: .7rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; white-space: nowrap; pointer-events: none; }
.bar-unique { width: 100%; border-radius: 6px 6px 0 0; background: #c7d2fe; min-height: 2px; }
.bar-label { font-size: .65rem; color: #94a3b8; writing-mode: vertical-rl; transform: rotate(180deg); height: 36px; text-align: center; }

/* Two-col layout */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
@media(max-width: 900px) { .two-col { grid-template-columns: 1fr; } }

/* Country list */
.country-row { display: flex; align-items: center; gap: .75rem; padding: .6rem 0; border-bottom: 1px solid #f8fafc; }
.country-row:last-child { border-bottom: none; }
.flag { font-size: 1.4rem; width: 30px; text-align: center; flex-shrink: 0; }
.country-name { flex: 1; font-size: .9rem; font-weight: 600; color: #374151; }
.country-bar-wrap { width: 120px; background: #f1f5f9; border-radius: 99px; height: 6px; }
.country-bar { height: 6px; border-radius: 99px; background: linear-gradient(90deg, #4f46e5, #7c3aed); }
.country-count { font-size: .85rem; font-weight: 800; color: #4f46e5; min-width: 36px; text-align: right; }

/* Page type badges */
.pt-grid { display: flex; flex-wrap: wrap; gap: .5rem; }
.pt-badge {
    display: flex; align-items: center; gap: .4rem;
    padding: .5rem 1rem; border-radius: 99px;
    font-size: .82rem; font-weight: 700;
    background: #f1f5f9; color: #374151;
}
.pt-badge .cnt { background: #4f46e5; color: #fff; border-radius: 99px; padding: .1rem .55rem; font-size: .75rem; }

/* Recent table */
.visit-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.visit-table th { text-align: left; color: #94a3b8; font-size: .72rem; text-transform: uppercase; letter-spacing: .8px; font-weight: 700; padding: .5rem .75rem; border-bottom: 2px solid #f1f5f9; }
.visit-table td { padding: .55rem .75rem; border-bottom: 1px solid #f8fafc; color: #374151; vertical-align: middle; }
.visit-table tr:hover td { background: #f8fafc; }
.ip-badge { background: #eef2ff; color: #4f46e5; border-radius: 6px; padding: .15rem .5rem; font-family: monospace; font-size: .8rem; }
.pt-pill { background: #dcfce7; color: #166534; border-radius: 99px; padding: .1rem .6rem; font-size: .75rem; font-weight: 700; }
.pt-pill.doctor { background: #dbeafe; color: #1d4ed8; }
.pt-pill.medicine { background: #fef3c7; color: #92400e; }
.pt-pill.home { background: #f0fdf4; color: #166534; }
.pt-pill.blog { background: #fae8ff; color: #7e22ce; }
.pt-pill.other { background: #f1f5f9; color: #64748b; }
.table-wrap { overflow-x: auto; }

/* Legend */
.chart-legend { display: flex; gap: 1.25rem; margin-bottom: .75rem; }
.leg { display: flex; align-items: center; gap: .4rem; font-size: .8rem; color: #64748b; font-weight: 600; }
.leg-dot { width: 12px; height: 12px; border-radius: 3px; }
</style>

<div class="va-wrap">

    {{-- Header --}}
    <div class="va-header">
        <h1>📊 Visitor Analytics <span class="va-badge">Live</span></h1>
        <div style="font-size:.82rem;color:#94a3b8;">Geo resolved on load · Last updated: {{ now()->format('d M Y, h:i A') }}</div>
    </div>

    {{-- Stat Cards --}}
    <div class="stat-grid">
        <div class="stat-card" style="--color:#4f46e5;">
            <div class="icon">👁️</div>
            <div class="label">Today's Visits</div>
            <div class="val">{{ number_format($todayCount) }}</div>
            <div class="sub">{{ number_format($todayUnique) }} unique IPs</div>
        </div>
        <div class="stat-card" style="--color:#06b6d4;">
            <div class="icon">📅</div>
            <div class="label">Yesterday</div>
            <div class="val">{{ number_format($yesterdayCount) }}</div>
            <div class="sub">{{ number_format($yesterdayUnique) }} unique IPs</div>
        </div>
        <div class="stat-card" style="--color:#10b981;">
            <div class="icon">📆</div>
            <div class="label">This Week</div>
            <div class="val">{{ number_format($weekCount) }}</div>
            <div class="sub">{{ number_format($weekUnique) }} unique IPs</div>
        </div>
        <div class="stat-card" style="--color:#f59e0b;">
            <div class="icon">🌐</div>
            <div class="label">All Time</div>
            <div class="val">{{ number_format($totalCount) }}</div>
            <div class="sub">Total recorded visits</div>
        </div>
    </div>

    {{-- 14-day Bar Chart --}}
    <div class="section-card">
        <div class="section-title">📈 Last 14 Days Traffic</div>
        <div class="chart-legend">
            <span class="leg"><span class="leg-dot" style="background:linear-gradient(#4f46e5,#7c3aed)"></span>Total visits</span>
            <span class="leg"><span class="leg-dot" style="background:#c7d2fe"></span>Unique IPs</span>
        </div>
        @php $maxVal = max(1, $chartDays->max('visits')); @endphp
        <div class="bar-chart">
            @foreach($chartDays as $day)
            @php
                $pct = round(($day['visits'] / $maxVal) * 100);
                $uPct = round(($day['unique'] / $maxVal) * 100);
            @endphp
            <div class="bar-col">
                <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;width:100%;gap:2px;">
                    <div class="bar-inner" style="height:{{ $pct }}%;" data-tip="{{ $day['visits'] }} visits"></div>
                    <div class="bar-unique" style="height:{{ $uPct }}%;"></div>
                </div>
                <div class="bar-label">{{ $day['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Country + Page Type --}}
    <div class="two-col">

        {{-- Country breakdown --}}
        <div class="section-card">
            <div class="section-title">🌍 Top Countries (This Week)</div>
            @php $maxC = max(1, $countryStats->max('visits')); @endphp
            @forelse($countryStats as $c)
            @php
                $flag = $c->country_code && $c->country_code !== '??' ? strtolower($c->country_code) : '';
                $flagEmoji = $flag ? mb_convert_encoding(
                    '&#' . (0x1F1E6 + ord($flag[0]) - ord('a')) . ';&#' . (0x1F1E6 + ord($flag[1]) - ord('a')) . ';',
                    'UTF-8', 'HTML-ENTITIES'
                ) : '🌐';
                $barPct = round(($c->visits / $maxC) * 100);
            @endphp
            <div class="country-row">
                <div class="flag">{{ $flagEmoji }}</div>
                <div class="country-name">{{ $c->country ?? 'Unknown' }}</div>
                <div class="country-bar-wrap">
                    <div class="country-bar" style="width:{{ $barPct }}%"></div>
                </div>
                <div class="country-count">{{ $c->visits }}</div>
            </div>
            @empty
            <p style="color:#94a3b8;font-size:.85rem;">No geo data yet. Refresh after visitors arrive.</p>
            @endforelse
        </div>

        {{-- Page type --}}
        <div class="section-card">
            <div class="section-title">📄 Today's Page Types</div>
            @if($pageTypeStats->isEmpty())
                <p style="color:#94a3b8;font-size:.85rem;">No visits recorded today yet.</p>
            @else
            <div class="pt-grid">
                @foreach($pageTypeStats as $pt)
                @php
                    $icons = ['home'=>'🏠','doctor'=>'🩺','doctors-list'=>'👨‍⚕️','medicine'=>'💊','medicines-list'=>'🔍','blog'=>'📝','static'=>'📋','other'=>'🔗','medicine-index'=>'📑'];
                    $icon = $icons[$pt->page_type] ?? '🔗';
                @endphp
                <div class="pt-badge">
                    {{ $icon }} {{ $pt->page_type }}
                    <span class="cnt">{{ $pt->cnt }}</span>
                </div>
                @endforeach
            </div>
            {{-- Mini breakdown bar --}}
            @php $totalPt = $pageTypeStats->sum('cnt'); @endphp
            <div style="margin-top:1.25rem;">
                @foreach($pageTypeStats as $pt)
                @php $pct = round(($pt->cnt / max(1,$totalPt)) * 100); @endphp
                <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;">
                    <div style="width:80px;font-size:.78rem;color:#64748b;font-weight:600;text-align:right;">{{ $pt->page_type }}</div>
                    <div style="flex:1;background:#f1f5f9;border-radius:99px;height:8px;">
                        <div style="height:8px;border-radius:99px;background:linear-gradient(90deg,#4f46e5,#7c3aed);width:{{ $pct }}%;"></div>
                    </div>
                    <div style="font-size:.78rem;font-weight:800;color:#4f46e5;min-width:32px;">{{ $pct }}%</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Recent Visits Table --}}
    <div class="section-card">
        <div class="section-title">🕐 Recent Visits (Last 100)</div>
        <div class="table-wrap">
            <table class="visit-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>IP</th>
                        <th>Country</th>
                        <th>City</th>
                        <th>Page Type</th>
                        <th>URL</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentVisits as $i => $v)
                    @php
                        $flag2 = $v->country_code ? strtolower($v->country_code) : '';
                        $flagE2 = $flag2 && $flag2 !== '??' ? mb_convert_encoding(
                            '&#' . (0x1F1E6 + ord($flag2[0]) - ord('a')) . ';&#' . (0x1F1E6 + ord($flag2[1]) - ord('a')) . ';',
                            'UTF-8', 'HTML-ENTITIES'
                        ) : '🌐';
                        $pillClass = in_array($v->page_type, ['doctor','medicine','home','blog']) ? $v->page_type : 'other';
                    @endphp
                    <tr>
                        <td style="color:#cbd5e1;">{{ $i + 1 }}</td>
                        <td><span class="ip-badge">{{ $v->ip ?? '—' }}</span></td>
                        <td>{{ $flagE2 }} {{ $v->country ?? '—' }}</td>
                        <td style="color:#64748b;">{{ $v->city ?? '—' }}</td>
                        <td><span class="pt-pill {{ $pillClass }}">{{ $v->page_type ?? 'other' }}</span></td>
                        <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#475569;" title="{{ $v->url }}">
                            {{ $v->url ?? '—' }}
                        </td>
                        <td style="color:#94a3b8;white-space:nowrap;">{{ $v->created_at ? $v->created_at->diffForHumans() : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
