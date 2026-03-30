@extends('layouts.app')

@section('title', 'Medicine Directory | eHealthFinder')
@section('meta_description', 'Search the comprehensive medicine index of Bangladesh. Find drug prices, generic names, companies, and antibiotic status.')

@section('content')

<style>
.mdx-page-title { font-size: 2.2rem; font-weight: 800; color: #0f172a; text-align: center; margin-bottom: 0.5rem; }
.mdx-page-subtitle { text-align: center; color: #64748b; font-size: 1.1rem; margin-bottom: 2.5rem; }
.mdx-search-box {
    background: white; padding: 0.5rem 0.5rem 0.5rem 1rem; border-radius: 50px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06); display: flex; align-items: center;
    max-width: 800px; margin: 0 auto 3rem; border: 1px solid #e2e8f0;
}
.mdx-search-input { flex: 1; border: none; outline: none; padding: 0.8rem 1rem; font-size: 1.1rem; color: #334155; background: transparent; }
.mdx-search-btn {
    background: #1d4ed8; color: white; border: none; padding: 1.1rem 2.5rem;
    border-radius: 50px; font-weight: 800; font-size: 1.05rem; cursor: pointer; transition: background 0.2s;
}
.mdx-search-btn:hover { background: #1e3a8a; }

.mdx-table-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; overflow: hidden; }
.mdx-table { width: 100%; border-collapse: collapse; text-align: left; }
.mdx-table th { background: #f8fafc; padding: 1.2rem 1.5rem; font-weight: 800; color: #475569; border-bottom: 2px solid #e2e8f0; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; }
.mdx-table td { padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #334155; }
.mdx-table tr:hover { background: #f8fafc; }
.mdx-med-name { font-size: 1.2rem; font-weight: 800; color: #1d4ed8; text-decoration: none; display: block; margin-bottom: 0.3rem;}
.mdx-med-name:hover { color: #1e3a8a; }
.mdx-med-form { font-size: 0.85rem; color: #64748b; font-weight: 700; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 0.3rem 0.8rem; border-radius: 50px; display: inline-block; }
.mdx-antibiotic { font-size: 0.75rem; font-weight: 800; color: #dc2626; background: #fee2e2; padding: 0.3rem 0.6rem; border-radius: 6px; display: inline-block; text-transform: uppercase; margin-bottom: 0.5rem;}
</style>

<div class="breadcrumb" style="margin-bottom: 2rem;">
    <a href="{{ route('home') }}">Home</a> ›
    <span style="color: #64748b;">Medicines</span>
</div>

<h1 class="mdx-page-title">Medicine Database</h1>
<p class="mdx-page-subtitle">Search comprehensive medicine information across Bangladesh</p>

<form method="GET" class="mdx-search-box">
    <span style="font-size: 1.3rem; margin-left:1rem; color:#94a3b8;">🔍</span>
    <input type="text" name="q" value="{{ request('q') }}" class="mdx-search-input" placeholder="Search brand or generic name...">
    <button type="submit" class="mdx-search-btn">Search &rarr;</button>
</form>

<div class="mdx-table-card">
    <table class="mdx-table">
        <thead>
            <tr>
                <th>Brand Details</th>
                <th>Generic Name</th>
                <th>Company</th>
                <th>Price Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $brand)
            <tr>
                <td>
                    @if($brand->is_antibiotic)<span class="mdx-antibiotic">⚠️ Antibiotic</span><br>@endif
                    <a href="{{ route('medicine.show', ['id' => $brand->id, 'slug' => Str::slug($brand->name)]) }}" class="mdx-med-name">
                        {{ $brand->name }}
                    </a>
                    <span class="mdx-med-form">{{ $brand->dosage_form }}</span>
                </td>
                <td style="font-weight: 600; color: #475569;">{{ $brand->generic ? $brand->generic->name : '-' }}</td>
                <td style="color: #64748b;">🏢 {{ $brand->company }}</td>
                <td><span style="font-weight: 800; color: #0f172a;">{{ $brand->price }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding: 4rem; text-align:center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                    <h3 style="color:#0f172a;">No medicines found</h3>
                    <p style="color:#64748b;">Try searching for a different brand or generic name.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 3rem; display: flex; justify-content: center;">
    {{ $brands->appends(request()->query())->links() }}
</div>
@endsection
