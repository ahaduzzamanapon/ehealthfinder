{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    
    <!-- Static Pages -->
    <url>
        <loc>{{ url('/') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/blog') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>

    <!-- Blog Posts -->
    @foreach ($posts as $post)
        <url>
            <loc>{{ url('/' . $post->slug) }}</loc>
            <lastmod>{{ $post->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.9</priority>
            @if($post->featured_image)
            <image:image>
                <image:loc>{{ url(Storage::url($post->featured_image)) }}</image:loc>
                <image:title>{{ $post->seo_title ?? $post->title }}</image:title>
            </image:image>
            @endif
        </url>
    @endforeach

</urlset>
