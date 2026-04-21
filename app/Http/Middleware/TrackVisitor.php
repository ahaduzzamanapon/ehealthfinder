<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PageVisit;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Skip non-GET, AJAX, admin, api, assets
        if (
            !$request->isMethod('GET') ||
            $request->ajax() ||
            $request->wantsJson() ||
            $request->is('admin/*') ||
            $request->is('api/*') ||
            $request->is('cron/*') ||
            $request->is('sitemap*') ||
            $request->is('robots.txt') ||
            str_contains($request->path(), '.')
        ) {
            return $response;
        }

        // Skip known bots
        $ua = strtolower($request->userAgent() ?? '');
        foreach (['bot', 'crawl', 'spider', 'slurp', 'wget', 'curl', 'python', 'go-http', 'httpclient'] as $bot) {
            if (str_contains($ua, $bot)) return $response;
        }

        $path = $request->path();

        // Detect page type
        $pageType = 'other';
        if ($path === '/')                                   $pageType = 'home';
        elseif (str_starts_with($path, 'doctor/'))          $pageType = 'doctor';
        elseif ($path === 'doctors')                        $pageType = 'doctors-list';
        elseif (str_starts_with($path, 'medicine/'))        $pageType = 'medicine';
        elseif ($path === 'medicines')                      $pageType = 'medicines-list';
        elseif ($path === 'medicine-index')                 $pageType = 'medicine-index';
        elseif (str_starts_with($path, 'blog'))             $pageType = 'blog';
        elseif (in_array($path, ['about','privacy','terms','disclaimer','refund-policy'])) $pageType = 'static';

        try {
            PageVisit::create([
                'ip'           => $request->ip(),
                'url'          => substr($request->url(), 0, 500),
                'user_agent'   => substr($request->userAgent() ?? '', 0, 255),
                'visited_date' => now()->toDateString(),
                'page_type'    => $pageType,
            ]);
        } catch (\Throwable $e) {
            // Never break site for analytics
        }

        return $response;
    }
}
