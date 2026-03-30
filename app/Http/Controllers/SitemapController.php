<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Brand;
use App\Models\Location;
use App\Models\Specialty;
use App\Models\BlogPost;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    private $chunkSize = 1000;

    public function index()
    {
        $sitemaps = [];
        $now = now()->toAtomString();

        // 1. General pages (home, about, etc.)
        $sitemaps[] = route('sitemap.show', ['type' => 'general', 'page' => 1]);

        // 2. Doctors
        $doctorCount = Doctor::count();
        $docPages = ceil($doctorCount / $this->chunkSize);
        for ($i = 1; $i <= $docPages; $i++) {
            $sitemaps[] = route('sitemap.show', ['type' => 'doctors', 'page' => $i]);
        }

        // 3. Medicines
        $medicineCount = Brand::count();
        $medPages = ceil($medicineCount / $this->chunkSize);
        for ($i = 1; $i <= $medPages; $i++) {
            $sitemaps[] = route('sitemap.show', ['type' => 'medicines', 'page' => $i]);
        }

        // 4. Locations (Searches by City)
        $sitemaps[] = route('sitemap.show', ['type' => 'locations', 'page' => 1]);

        // 5. Specialties (Searches by Specialty)
        $sitemaps[] = route('sitemap.show', ['type' => 'specialties', 'page' => 1]);

        // 6. Combinations (Specialty in City)
        $combosCount = DB::table('doctors')->select('location_id', 'specialty_id')->distinct()
            ->whereNotNull('location_id')->whereNotNull('specialty_id')->get()->count();
        $comboPages = ceil($combosCount / $this->chunkSize);
        for ($i = 1; $i <= $comboPages; $i++) {
            $sitemaps[] = route('sitemap.show', ['type' => 'combinations', 'page' => $i]);
        }

        // 7. Blog Posts
        $blogCount = BlogPost::where('is_published', 1)->count();
        $blogPages = ceil($blogCount / $this->chunkSize);
        for ($i = 1; $i <= $blogPages; $i++) {
            $sitemaps[] = route('sitemap.show', ['type' => 'blogs', 'page' => $i]);
        }

        $content = view('sitemap-index', compact('sitemaps', 'now'))->render();
        return response($content, 200)->header('Content-Type', 'application/xml');
    }

    public function show($type, $page)
    {
        // Cache the chunk for 24 hours to prevent db hits from crawlers
        $cacheKey = "sitemap_{$type}_{$page}";
        $urls = Cache::remember($cacheKey, 60 * 60 * 24, function () use ($type, $page) {
            $offset = ($page - 1) * $this->chunkSize;
            $items = [];
            $now = now()->toAtomString();

            if ($type === 'general' && $page == 1) {
                $items = [
                    ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
                    ['loc' => route('doctors.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
                    ['loc' => route('medicines.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
                    ['loc' => route('blog.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
                    ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.6'],
                    ['loc' => route('privacy'), 'changefreq' => 'monthly', 'priority' => '0.6'],
                    ['loc' => route('disclaimer'), 'changefreq' => 'monthly', 'priority' => '0.6'],
                    ['loc' => route('terms'), 'changefreq' => 'monthly', 'priority' => '0.6'],
                ];
            } 
            elseif ($type === 'doctors') {
                $docs = Doctor::with(['specialty', 'location'])->select('id', 'name', 'specialty_id', 'location_id', 'updated_at')
                    ->offset($offset)->limit($this->chunkSize)->get();
                foreach ($docs as $doc) {
                    $items[] = [
                        'loc' => route('doctor.show', ['idslug' => $doc->seo_slug]),
                        'lastmod' => $doc->updated_at ? $doc->updated_at->toAtomString() : $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.8'
                    ];
                }
            } 
            elseif ($type === 'medicines') {
                $meds = Brand::select('id', 'name', 'slug', 'updated_at')
                    ->offset($offset)->limit($this->chunkSize)->get();
                foreach ($meds as $med) {
                    $items[] = [
                        'loc' => route('medicine.show', ['id' => $med->id, 'slug' => $med->slug ?? Str::slug($med->name)]),
                        'lastmod' => $med->updated_at ? $med->updated_at->toAtomString() : $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.7'
                    ];
                }
            }
            elseif ($type === 'locations' && $page == 1) {
                // All location-only searches ("best doctor in rangpur")
                $locations = DB::table('doctors')->select('location_id')->distinct()->whereNotNull('location_id')->get();
                foreach($locations as $l) {
                    $items[] = [
                        'loc' => \App\Helpers\SeoHelper::getSeoUrl(null, $l->location_id),
                        'changefreq' => 'weekly',
                        'priority' => '0.8'
                    ];
                }
            }
            elseif ($type === 'specialties' && $page == 1) {
                // All specialty-only searches ("best doctor for Anesthesiologist")
                $specialties = DB::table('doctors')->select('specialty_id')->distinct()->whereNotNull('specialty_id')->get();
                foreach($specialties as $s) {
                    $items[] = [
                        'loc' => \App\Helpers\SeoHelper::getSeoUrl($s->specialty_id, null),
                        'changefreq' => 'weekly',
                        'priority' => '0.8'
                    ];
                }
            }
            elseif ($type === 'combinations') {
                // "Anesthesiologist in Rangpur"
                $combos = DB::table('doctors')
                    ->select('location_id', 'specialty_id')->distinct()
                    ->whereNotNull('location_id')->whereNotNull('specialty_id')
                    ->offset($offset)->limit($this->chunkSize)->get();
                
                foreach($combos as $c) {
                    $items[] = [
                        'loc' => \App\Helpers\SeoHelper::getSeoUrl($c->specialty_id, $c->location_id),
                        'changefreq' => 'weekly',
                        'priority' => '0.9'
                    ];
                }
            }
            elseif ($type === 'blogs') {
                $blogs = BlogPost::select('slug', 'updated_at')->where('is_published', 1)
                    ->offset($offset)->limit($this->chunkSize)->get();
                foreach($blogs as $blog) {
                    $items[] = [
                        'loc' => route('blog.show', ['slug' => $blog->slug]),
                        'lastmod' => $blog->updated_at ? $blog->updated_at->toAtomString() : $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.8'
                    ];
                }
            }

            return $items;
        });

        if (empty($urls)) {
            abort(404);
        }

        $content = view('sitemap-chunk', compact('urls'))->render();
        return response($content, 200)->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $content = "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml') . "\n";
        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}

