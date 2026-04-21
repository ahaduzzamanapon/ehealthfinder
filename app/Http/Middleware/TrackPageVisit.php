<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;

class TrackPageVisit
{
    // Skip these paths entirely
    private array $skipPrefixes = [
        '/admin', '/api', '/cron', '/sitemap', '/robots', '/_ignition',
        '/favicon', '/logo', '/css', '/js', '/images', '/fonts', '/storage',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only track GET requests for HTML pages
        if ($request->isMethod('GET') && !$request->ajax() && !$request->wantsJson()) {
            $path = $request->path();

            // Skip admin / asset paths
            foreach ($this->skipPrefixes as $prefix) {
                if (str_starts_with('/' . $path, $prefix)) {
                    return $response;
                }
            }

            // Skip bot/crawler user agents
            $ua = strtolower($request->userAgent() ?? '');
            $bots = ['bot', 'crawl', 'spider', 'slurp', 'wget', 'curl', 'python', 'httpclient'];
            foreach ($bots as $bot) {
                if (str_contains($ua, $bot)) {
                    return $response;
                }
            }

            $ip = $request->ip();

            // Detect page type from URL
            $pageType = 'other';
            if ($path === '/' || $path === '')            $pageType = 'home';
            elseif (str_starts_with($path, 'doctor/'))   $pageType = 'doctor';
            elseif (str_starts_with($path, 'doctors'))   $pageType = 'doctors-list';
            elseif (str_starts_with($path, 'medicine/')) $pageType = 'medicine';
            elseif (str_starts_with($path, 'medicines')) $pageType = 'medicines-list';
            elseif (str_starts_with($path, 'blog') || str_starts_with($path, 'b/')) $pageType = 'blog';

            try {
                PageVisit::create([
                    'ip'           => $ip,
                    'url'          => $request->url(),
                    'user_agent'   => substr($request->userAgent() ?? '', 0, 255),
                    'visited_date' => now()->toDateString(),
                    'page_type'    => $pageType,
                ]);
            } catch (\Throwable $e) {
                // Silently fail — never break the page for analytics
            }
        }

        return $response;
    }
}
