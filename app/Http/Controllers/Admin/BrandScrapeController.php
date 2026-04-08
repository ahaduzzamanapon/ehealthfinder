<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandScrapeController extends Controller
{
    /**
     * Called by cron every 10 minutes.
     * Picks ONE pending brand, scrapes medex.com.bd, saves bangla_name + strength.
     *
     * URL: /cron/scrape-brand?token=YOUR_SECRET_TOKEN
     */
    public function scrapeOne()
    {
        // ─── Token protection ────────────────────────────────────────
        $secret = env('SCRAPE_TOKEN', 'change_me_in_env');
        if (request('token') !== $secret) {
            abort(403, 'Forbidden');
        }

        // ─── Pick one brand: pending first, then retry failed ─────────
        // failed brands (e.g. captcha/network error) get re-tried automatically
        $brand = Brand::where('scrape_status', 'pending')
            ->whereNotNull('medex_id')
            ->orderBy('id')
            ->first();

        // If no pending left, retry a failed one
        if (!$brand) {
            $brand = Brand::where('scrape_status', 'failed')
                ->whereNotNull('medex_id')
                ->orderBy('id')
                ->first();
        }

        if (!$brand) {
            return response()->json([
                'status'  => 'all_done',
                'message' => 'No more pending brands to scrape.',
            ]);
        }

        // ─── Scrape the brand page ────────────────────────────────────
        $medexId = $brand->medex_id;

        try {
            // 1) Fetch the English brand page to discover the slug
            $enUrl  = "https://medex.com.bd/brands/{$medexId}/";
            $enHtml = $this->fetchUrl($enUrl);

            // ─── Captcha / Security Check detection ──────────────────────
            // If medex returns a bot/captcha page, reset to pending and bail out
            if ($this->isCaptchaPage($enHtml)) {
                $brand->scrape_status = 'pending';
                $brand->save();
                return response()->json([
                    'status'   => 'captcha',
                    'brand_id' => $brand->id,
                    'message'  => 'Security check detected on EN page. Reset to pending for retry.',
                ]);
            }

            // Extract slug from the canonical/redirect URL
            // medex redirects to the full slug URL, so grab it from <link rel="canonical">
            $slug = $this->extractSlug($enHtml, $medexId);

            // 2) Strength: parse from the English page title like "Normens 5 mg Tablet"
            $strength = $this->extractStrength($enHtml, $brand->name);

            // 3) Fetch the Bangla page using the resolved slug
            $bnUrl    = "https://medex.com.bd/brands/{$medexId}/{$slug}/bn";
            $bnHtml   = $this->fetchUrl($bnUrl);

            // ─── Captcha check on Bangla page too ───────────────────────
            if ($this->isCaptchaPage($bnHtml)) {
                $brand->scrape_status = 'pending';
                $brand->save();
                return response()->json([
                    'status'   => 'captcha',
                    'brand_id' => $brand->id,
                    'message'  => 'Security check detected on BN page. Reset to pending for retry.',
                ]);
            }

            $banglaName = $this->extractBanglaName($bnHtml);

            // ─── Save ─────────────────────────────────────────────────
            $brand->bangla_name   = $banglaName ?: null;
            $brand->strength      = $strength   ?: null;
            $brand->scrape_status = 'done';
            $brand->save();

            return response()->json([
                'status'      => 'success',
                'brand_id'    => $brand->id,
                'name'        => $brand->name,
                'bangla_name' => $banglaName,
                'strength'    => $strength,
                'medex_id'    => $medexId,
            ]);

        } catch (\Throwable $e) {
            $brand->scrape_status = 'failed';
            $brand->save();

            return response()->json([
                'status'   => 'error',
                'brand_id' => $brand->id,
                'message'  => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Helper: detect captcha / security check page ─────────────
    private function isCaptchaPage(string $html): bool
    {
        $lower = strtolower($html);
        return str_contains($lower, 'security check')
            || str_contains($lower, 'captcha')
            || str_contains($lower, 'cloudflare')
            || str_contains($lower, 'just a moment')
            || str_contains($lower, 'enable javascript and cookies')
            || str_contains($lower, 'cf-browser-verification')
            || (strlen($html) < 2000 && str_contains($lower, 'checking your browser'));
    }

    // ─── Helper: fetch URL with a browser-like User-Agent ─────────────
    private function fetchUrl(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept-Language: en-US,en;q=0.9',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);
        $html = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($html === false) {
            throw new \RuntimeException("cURL error for {$url}: {$err}");
        }
        return $html;
    }

    // ─── Helper: extract slug from canonical link ───────────────────
    private function extractSlug(string $html, $medexId): string
    {
        // <link rel="canonical" href="https://medex.com.bd/brands/5921/normens-5-mg-tablet/" />
        if (preg_match('#medex\.com\.bd/brands/' . preg_quote($medexId, '#') . '/([^/"]+)#i', $html, $m)) {
            return trim($m[1], '/');
        }
        // Fallback: try og:url
        if (preg_match('#og:url.*?content=["\']https://medex\.com\.bd/brands/' . preg_quote($medexId, '#') . '/([^/"]+)#i', $html, $m)) {
            return trim($m[1], '/');
        }
        return 'index'; // last resort
    }

    // ─── Helper: extract strength from div[title="Strength"] ────────
    private function extractStrength(string $html, string $brandName): ?string
    {
        // <div title="Strength">\n   40 mg\n</div>
        if (preg_match('#<div[^>]+title=["\']Strength["\'][^>]*>\s*(.*?)\s*</div>#is', $html, $m)) {
            $val = trim(strip_tags($m[1]));
            if ($val !== '') return $val;
        }
        return null;
    }

    // ─── Helper: extract Bangla brand name from BN page h1 ─────────
    private function extractBanglaName(string $html): ?string
    {
        // <h1 class="page-heading-1-l brand">
        //   <span class="tx-0-95">ম্যাক্সপ্রো মাপ্স <small ...>ট্যাবলেট</small></span>
        // </h1>
        // We want only the text BEFORE the <small> tag (the actual Bangla brand name)
        if (preg_match('#<h1[^>]+class=["\'][^"\']*(page-heading-1-l|brand)[^"\'][^>]*>.*?<span[^>]*>(.*?)</span>#is', $html, $m)) {
            // Remove the <small>...</small> dosage form part
            $text = preg_replace('#<small[^>]*>.*?</small>#is', '', $m[2]);
            $name = trim(strip_tags($text));
            if ($name !== '') return $name;
        }
        return null;
    }

    // ─── Progress endpoint ──────────────────────────────────────────
    public function progress()
    {
        $secret = env('SCRAPE_TOKEN', 'change_me_in_env');
        if (request('token') !== $secret) {
            abort(403, 'Forbidden');
        }

        $total   = Brand::whereNotNull('medex_id')->count();
        $done    = Brand::where('scrape_status', 'done')->count();
        $failed  = Brand::where('scrape_status', 'failed')->count();
        $pending = Brand::where('scrape_status', 'pending')->whereNotNull('medex_id')->count();

        return response()->json([
            'total'   => $total,
            'done'    => $done,
            'failed'  => $failed,
            'pending' => $pending,
            'percent' => $total > 0 ? round(($done / $total) * 100, 1) : 0,
        ]);
    }
}
