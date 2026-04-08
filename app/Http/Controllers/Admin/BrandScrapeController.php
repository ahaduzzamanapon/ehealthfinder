<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandScrapeController extends Controller
{
    /**
     * All sections to scrape from medex — key = div id, value = brand column prefix
     * EN column: key_en, BN column: key_bn
     */
    private array $sections = [
        'indications'       => 'indications',
        'mode_of_action'    => 'mode_of_action',
        'dosage'            => 'dosage',
        'administration'    => 'administration',
        'interaction'       => 'interaction',
        'contraindications' => 'contraindications',
        'side_effects'      => 'side_effects',
        'pregnancy_cat'     => 'pregnancy_cat',
        'precautions'       => 'precautions',
        'pediatric_uses'    => 'pediatric_uses',
        'storage_conditions'=> 'storage_conditions',
    ];

    /**
     * Cron endpoint: pick ONE brand, scrape EN + BN, save all fields.
     * URL: /cron/scrape-brand?token=YOUR_SECRET_TOKEN
     */
    public function scrapeOne()
    {
        $secret = env('SCRAPE_TOKEN', 'change_me_in_env');
        if (request('token') !== $secret) {
            abort(403, 'Forbidden');
        }

        // Pick pending first, then retry failed
        $brand = Brand::where('scrape_status', 'pending')
            ->whereNotNull('medex_id')
            ->orderBy('id')
            ->first();

        if (!$brand) {
            $brand = Brand::where('scrape_status', 'failed')
                ->whereNotNull('medex_id')
                ->orderBy('id')
                ->first();
        }

        if (!$brand) {
            return response()->json([
                'status'  => 'all_done',
                'message' => 'No more brands to scrape.',
            ]);
        }

        $medexId = $brand->medex_id;

        try {
            // ── 1. Fetch EN page ──────────────────────────────────────
            $enUrl  = "https://medex.com.bd/brands/{$medexId}/";
            $enHtml = $this->fetchUrl($enUrl);

            if ($this->isCaptchaPage($enHtml)) {
                $brand->scrape_status = 'pending';
                $brand->save();
                return response()->json(['status' => 'captcha', 'brand_id' => $brand->id,
                    'message' => 'Security check on EN page. Kept as pending for retry.']);
            }

            // Extract slug for BN URL
            $slug = $this->extractSlug($enHtml, $medexId);

            // ── 2. Fetch BN page ──────────────────────────────────────
            $bnUrl  = "https://medex.com.bd/brands/{$medexId}/{$slug}/bn";
            $bnHtml = $this->fetchUrl($bnUrl);

            if ($this->isCaptchaPage($bnHtml)) {
                $brand->scrape_status = 'pending';
                $brand->save();
                return response()->json(['status' => 'captcha', 'brand_id' => $brand->id,
                    'message' => 'Security check on BN page. Kept as pending for retry.']);
            }

            // ── 3. Extract fields ─────────────────────────────────────
            $data = [
                'strength'    => $this->extractStrength($enHtml),
                'bangla_name' => $this->extractBanglaName($bnHtml),
                'scrape_status' => 'done',
            ];

            // Extract each clinical section from both pages
            foreach ($this->sections as $sectionId => $colPrefix) {
                $data[$colPrefix . '_en'] = $this->extractSection($enHtml, $sectionId);
                $data[$colPrefix . '_bn'] = $this->extractSection($bnHtml, $sectionId);
            }

            // ── 4. Save ───────────────────────────────────────────────
            $brand->fill($data);
            $brand->save();

            return response()->json([
                'status'      => 'success',
                'brand_id'    => $brand->id,
                'name'        => $brand->name,
                'bangla_name' => $data['bangla_name'],
                'strength'    => $data['strength'],
                'sections_en' => array_filter(array_map(fn($k) => $data[$k.'_en'] ? '✓' : '✗', array_values($this->sections))),
                'sections_bn' => array_filter(array_map(fn($k) => $data[$k.'_bn'] ? '✓' : '✗', array_values($this->sections))),
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

    // ─── Extract a section's content by its div id ─────────────────────
    // Medex structure:
    //   <div id="SECTION_ID"><h3>...</h3></div>
    //   <div class="ac-body">CONTENT (may have min-str-block > full-str)</div>
    private function extractSection(string $html, string $sectionId): ?string
    {
        // Find ac-body that follows the section's div id
        $pattern = '#<div[^>]+id=["\']' . preg_quote($sectionId, '#') . '["\'][^>]*>.*?</div>\s*<div[^>]+class=["\']ac-body["\'][^>]*>(.*?)</div>\s*(?=<div|$)#is';

        if (!preg_match($pattern, $html, $m)) {
            // Fallback: simpler pattern
            $pattern2 = '#id=["\']' . preg_quote($sectionId, '#') . '["\'].*?<div class=["\']ac-body["\']>(.*?)</div>#is';
            if (!preg_match($pattern2, $html, $m)) {
                return null;
            }
        }

        $content = $m[1];

        // If it has a full-str block (the non-truncated version), extract that
        if (preg_match("#<div[^>]+class=['\"]full-str['\"][^>]*>(.*?)</div>\s*</div>#is", $content, $fm)) {
            $content = $fm[1];
        }

        // Remove min-str-block wrapper html (toggle buttons etc.)
        $content = preg_replace("#<div[^>]+class=['\"][^'\"]*min-str[^'\"]*['\"][^>]*>.*?</div>#is", '', $content);

        // Remove "Read more" spans
        $content = preg_replace("#<span[^>]+class=['\"][^'\"]*min-str-toggle[^'\"]*['\"][^>]*>.*?</span>#is", '', $content);

        $content = trim($content);
        if ($content === '') {
            return null;
        }

        // Decode HTML entities like &zwj; completely so it doesn't render as string literal in blade
        return html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ─── Detect captcha / security check page ─────────────────────────
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

    // ─── Fetch URL with browser-like headers ──────────────────────────
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

    // ─── Extract the medex slug from canonical link ────────────────────
    private function extractSlug(string $html, $medexId): string
    {
        if (preg_match('#medex\.com\.bd/brands/' . preg_quote($medexId, '#') . '/([^/"]+)#i', $html, $m)) {
            return trim($m[1], '/');
        }
        if (preg_match('#og:url.*?content=["\'](https://medex\.com\.bd/brands/' . preg_quote($medexId, '#') . '/([^/"]+))#i', $html, $m)) {
            return trim($m[2], '/');
        }
        return 'index';
    }

    // ─── Extract strength from <div title="Strength"> ─────────────────
    private function extractStrength(string $html): ?string
    {
        if (preg_match('#<div[^>]+title=["\']Strength["\'][^>]*>\s*(.*?)\s*</div>#is', $html, $m)) {
            $val = trim(strip_tags($m[1]));
            if ($val !== '') return $val;
        }
        return null;
    }

    // ─── Extract Bangla brand name from BN page h1 ────────────────────
    // <h1 class="page-heading-1-l brand">
    //   <span class="tx-0-95">ম্যাক্সপ্রো <small>ট্যাবলেট</small></span>
    // </h1>
    private function extractBanglaName(string $html): ?string
    {
        if (preg_match('#<h1[^>]+class=["\'][^"\']*brand[^"\']*["\'][^>]*>.*?<span[^>]*>(.*?)</span>#is', $html, $m)) {
            // Strip the <small> dosage form tag
            $text = preg_replace('#<small[^>]*>.*?</small>#is', '', $m[1]);
            $name = trim(strip_tags($text));
            if ($name !== '') return $name;
        }
        return null;
    }

    // ─── Progress endpoint ─────────────────────────────────────────────
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
