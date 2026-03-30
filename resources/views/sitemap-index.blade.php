<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($sitemaps as $url)
    <sitemap>
        <loc>{{ $url }}</loc>
        <lastmod>{{ $now }}</lastmod>
    </sitemap>
@endforeach
</sitemapindex>
