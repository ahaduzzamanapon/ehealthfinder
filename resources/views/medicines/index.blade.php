@extends('layouts.app')

@section('title', 'Medicine Directory | eHealthFinder')
@section('meta_description', 'Search the comprehensive medicine index of Bangladesh. Find drug prices, generic names, companies, and antibiotic status.')

@section('content')

@php
    $schemaList = [];
    foreach($brands->items() as $key => $brand) {
        $schemaList[] = [
            '@type'    => 'ListItem',
            'position' => $key + 1,
            'url'      => route('medicine.show', ['id' => $brand->id, 'slug' => Str::slug($brand->name)]),
            'name'     => $brand->name
        ];
    }
    $schemaJson = json_encode([
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => 'Medicine Directory Bangladesh',
        'description'     => 'Search the comprehensive medicine index of Bangladesh.',
        'url'             => route('medicines.index'),
        'numberOfItems'   => count($schemaList),
        'itemListElement' => $schemaList
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
@endphp

@section('schema')
<script type="application/ld+json">{!! $schemaJson !!}</script>
@endsection
<h1 style="margin-bottom: 2rem">Medicine Database</h1>

<form method="GET" class="card" style="display:flex; gap:1rem; margin-bottom: 3rem; max-width:800px; margin-left:auto; margin-right:auto">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search brand or generic name..." style="flex:1">
    <button type="submit" class="btn btn-primary" style="width: 150px">Search</button>
</form>

<div class="card" style="padding: 0; overflow:hidden">
    <table style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr style="background: var(--gray); border-bottom: 2px solid #cbd5e0;">
                <th style="padding: 1rem 1.5rem">Brand Name</th>
                <th style="padding: 1rem 1.5rem">Generic</th>
                <th style="padding: 1rem 1.5rem">Company</th>
                <th style="padding: 1rem 1.5rem">Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $brand)
            <tr style="border-bottom: 1px solid var(--gray);">
                <td style="padding: 1rem 1.5rem">
                    <a href="{{ route('medicine.show', ['id' => $brand->id, 'slug' => Str::slug($brand->name)]) }}" style="font-weight:600; font-size:1.1rem">
                        {{ $brand->name }}
                    </a>
                    <br><small class="text-muted">{{ $brand->dosage_form }}</small>
                    @if($brand->is_antibiotic)<span class="tag" style="background:#fed7d7; color:#c53030; font-size: 0.7rem; padding: 2px 6px">Antibiotic</span>@endif
                </td>
                <td style="padding: 1rem 1.5rem">{{ $brand->generic ? $brand->generic->name : '-' }}</td>
                <td style="padding: 1rem 1.5rem">{{ $brand->company }}</td>
                <td style="padding: 1rem 1.5rem">{{ $brand->price }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="padding: 2rem; text-align:center">No medicines found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top: 2rem;">
    {{ $brands->appends(request()->query())->links() }}
</div>
@endsection
