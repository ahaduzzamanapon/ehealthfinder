<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PageVisit;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        // Skip admin, api, assets
        if (
            $request->is('admin/*') ||
            $request->is('api/*') ||
            $request->is('sitemap*') ||
            $request->is('robots.txt') ||
            str_contains($request->path(), '.')
        ) {
            return $next($request);
        }

        $ip   = $request->ip();
        $date = now()->toDateString();

        // DB unique constraint on (ip, visited_date) prevents duplicates safely
        PageVisit::insertOrIgnore([
            'ip'           => $ip,
            'url'          => substr($request->path(), 0, 1000),
            'user_agent'   => substr($request->userAgent() ?? '', 0, 255),
            'visited_date' => $date,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return $next($request);
    }
}
