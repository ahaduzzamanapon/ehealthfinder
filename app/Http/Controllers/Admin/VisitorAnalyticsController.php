<?php

namespace App\Http\Controllers\Admin;

use App\Models\PageVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class VisitorAnalyticsController extends \App\Http\Controllers\Controller
{
    /**
     * Resolve geo for IPs that have no country yet — called when admin opens the page.
     * Uses ip-api.com (free, no key, 100 req/min limit).
     * Caches each IP result for 7 days.
     */
    private function resolveGeo(): void
    {
        // Grab up to 40 unresolved distinct IPs
        $unresolved = PageVisit::whereNull('country')
            ->whereNotNull('ip')
            ->select('ip')
            ->distinct()
            ->limit(40)
            ->pluck('ip');

        foreach ($unresolved as $ip) {
            $cacheKey = 'geo_ip_' . md5($ip);

            $geo = Cache::remember($cacheKey, now()->addDays(7), function () use ($ip) {
                try {
                    $res = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=country,countryCode,city,status");
                    if ($res->ok()) {
                        $data = $res->json();
                        if (($data['status'] ?? '') === 'success') {
                            return [
                                'country'      => $data['country']      ?? null,
                                'country_code' => $data['countryCode']  ?? null,
                                'city'         => $data['city']         ?? null,
                            ];
                        }
                    }
                } catch (\Throwable $e) {}
                return ['country' => 'Unknown', 'country_code' => '??', 'city' => null];
            });

            // Update all rows with this IP
            PageVisit::where('ip', $ip)->whereNull('country')->update($geo);
        }
    }

    public function index(Request $request)
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();

        // Unresolved count to show on button
        $unresolvedCount = PageVisit::whereNull('country')->whereNotNull('ip')->distinct('ip')->count('ip');

        // ── Visitor counts ───────────────────────────────────────
        $todayCount     = PageVisit::whereDate('visited_date', $today)->count();
        $yesterdayCount = PageVisit::whereDate('visited_date', $yesterday)->count();
        $weekCount      = PageVisit::whereBetween('visited_date', [$weekStart, $today])->count();
        $totalCount     = PageVisit::count();

        // Unique visitors (by IP) per period
        $todayUnique     = PageVisit::whereDate('visited_date', $today)->distinct('ip')->count('ip');
        $yesterdayUnique = PageVisit::whereDate('visited_date', $yesterday)->distinct('ip')->count('ip');
        $weekUnique      = PageVisit::whereBetween('visited_date', [$weekStart, $today])->distinct('ip')->count('ip');

        // ── Country breakdown (this week) ────────────────────────
        $countryStats = PageVisit::whereBetween('visited_date', [$weekStart, $today])
            ->whereNotNull('country')
            ->select('country', 'country_code', DB::raw('COUNT(*) as visits'), DB::raw('COUNT(DISTINCT ip) as unique_visitors'))
            ->groupBy('country', 'country_code')
            ->orderByDesc('visits')
            ->limit(20)
            ->get();

        // ── Daily chart (last 14 days) ───────────────────────────
        $chartDays = collect();
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $chartDays->push([
                'date'    => $date,
                'label'   => now()->subDays($i)->format('M d'),
                'visits'  => PageVisit::whereDate('visited_date', $date)->count(),
                'unique'  => PageVisit::whereDate('visited_date', $date)->distinct('ip')->count('ip'),
            ]);
        }

        // ── Page type breakdown (today) ──────────────────────────
        $pageTypeStats = PageVisit::whereDate('visited_date', $today)
            ->select('page_type', DB::raw('COUNT(*) as cnt'))
            ->groupBy('page_type')
            ->orderByDesc('cnt')
            ->get();

        // ── Recent visits (last 100) ────────────────────────────
        $recentVisits = PageVisit::orderByDesc('created_at')->limit(100)->get();

        $cronUrl = url('/cron/resolve-geo?token=' . env('SCRAPE_TOKEN'));

        return view('admin.visitors.index', compact(
            'todayCount', 'yesterdayCount', 'weekCount', 'totalCount',
            'todayUnique', 'yesterdayUnique', 'weekUnique',
            'countryStats', 'chartDays', 'pageTypeStats', 'recentVisits',
            'today', 'yesterday', 'unresolvedCount', 'cronUrl'
        ));
    }

    /** POST /admin/visitors/resolve-geo — manual button */
    public function resolveGeoAction()
    {
        $this->resolveGeo();
        return redirect()->route('admin.visitors.index')->with('success', 'Geo resolved for up to 40 IPs!');
    }

    /** GET /cron/resolve-geo?token=xxx — server cron endpoint */
    public function resolveGeoCron(Request $request)
    {
        if ($request->query('token') !== env('SCRAPE_TOKEN')) {
            abort(403, 'Unauthorized');
        }
        $this->resolveGeo();
        return response()->json(['status' => 'ok', 'resolved_at' => now()->toIso8601String()]);
    }
}
