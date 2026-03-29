<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    {{-- Static pages --}}
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('doctors.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('medicines.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>

    {{-- Doctor pages --}}
    @foreach($doctors as $doc)
    <url>
        <loc>{{ route('doctor.show', ['idslug' => $doc->id . '-' . Str::slug($doc->name)]) }}</loc>
        <lastmod>{{ $doc->updated_at ? $doc->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach

    {{-- Medicine pages --}}
    @foreach($medicines as $brand)
    <url>
        <loc>{{ route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug ?? Str::slug($brand->name)]) }}</loc>
        <lastmod>{{ $brand->updated_at ? $brand->updated_at->toAtomString() : now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    @endforeach

</urlset>
