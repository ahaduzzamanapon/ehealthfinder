<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Doctor;
use App\Models\Brand;
use App\Models\Generic;
use App\Models\Location;
use App\Models\Specialty;
use App\Models\Hospital;

class SearchController extends Controller
{
    public function home()
    {
        $locations  = Location::orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();
        $featuredDoctors = Doctor::with(['location', 'specialty'])->inRandomOrder()->take(6)->get();
        $featuredBrands  = Brand::with('generic')->inRandomOrder()->take(6)->get();

        $stats = [
            'doctors'    => Doctor::count(),
            'medicines'  => Brand::count(),
            'locations'  => Location::count(),
            'specialties'=> Specialty::count(),
        ];

        return view('home', compact('locations', 'specialties', 'featuredDoctors', 'featuredBrands', 'stats'));
    }

    public function searchDoctors(Request $request)
    {
        // 301 Redirect old parameterized URLs to clean SEO URLs
        if (!$request->filled('q') && ($request->filled('location_id') || $request->filled('specialty_id')) && !$request->routeIs('seo.url')) {
            $url = \App\Helpers\SeoHelper::getSeoUrl($request->specialty_id, $request->location_id);
            // Don't redirect if the helper returned the generic /doctors route to avoid loops
            if ($url !== route('doctors.index')) {
                return redirect($url, 301);
            }
        }

        $query = Doctor::with(['location', 'specialty']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                   ->orWhere('degrees', 'like', "%$q%")
                   ->orWhere('designation', 'like', "%$q%");
            });
        }
        if ($request->filled('location_id'))  $query->where('location_id', $request->location_id);
        if ($request->filled('specialty_id')) $query->where('specialty_id', $request->specialty_id);

        $doctors     = $query->paginate(20)->withQueryString();
        $locations   = Location::orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();

        // For dynamic SEO meta
        $selectedSpecialty = $request->filled('specialty_id')
            ? Specialty::find($request->specialty_id) : null;
        $selectedLocation  = $request->filled('location_id')
            ? Location::find($request->location_id) : null;

        return view('doctors.index', compact(
            'doctors', 'locations', 'specialties',
            'selectedSpecialty', 'selectedLocation'
        ));
    }

    public function searchMedicines(Request $request)
    {
        $query = Brand::with('generic');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qb) use ($q) {
                $qb->where('name', 'like', "%$q%")
                   ->orWhere('company', 'like', "%$q%")
                   ->orWhere('dosage_form', 'like', "%$q%")
                   ->orWhereHas('generic', fn($gq) => $gq->where('name', 'like', "%$q%"));
            });
        }
        if ($request->filled('antibiotic')) $query->where('is_antibiotic', 1);

        $brands = $query->paginate(20)->withQueryString();

        return view('medicines.index', compact('brands'));
    }

    /* ────── AUTOCOMPLETE ENDPOINTS ────── */

    public function suggestDoctors(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $results = Doctor::with(['specialty', 'location'])
            ->where('name', 'like', "%$q%")
            ->take(8)->get()
            ->map(fn($d) => [
                'id'       => $d->id,
                'label'    => $d->name,
                'sub'      => $d->specialty?->name . ($d->location ? ' · ' . $d->location->name : ''),
                'url'      => route('doctor.show', ['idslug' => $d->seo_slug]),
                'type'     => 'doctor',
            ]);

        return response()->json($results);
    }

    public function suggestMedicines(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $brands = Brand::with('generic')
            ->where('name', 'like', "%$q%")
            ->take(5)->get()
            ->map(fn($b) => [
                'id'    => $b->id,
                'label' => $b->name,
                'sub'   => ($b->generic?->name ?? '') . ' · ' . $b->dosage_form,
                'url'   => route('medicine.show', ['id' => $b->id, 'slug' => $b->slug]),
                'type'  => 'medicine',
            ]);

        $generics = Generic::where('name', 'like', "%$q%")
            ->take(4)->get()
            ->map(fn($g) => [
                'id'    => $g->id,
                'label' => $g->name,
                'sub'   => 'Generic · ' . $g->brands()->count() . ' brands',
                'url'   => route('medicines.index', ['q' => $g->name]),
                'type'  => 'generic',
            ]);

        return response()->json($brands->merge($generics)->values());
    }

    public function suggestAll(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) return response()->json([]);

        $doctors = Doctor::with(['specialty', 'location'])
            ->where('name', 'like', "%$q%")
            ->take(4)->get()
            ->map(fn($d) => [
                'label' => $d->name,
                'sub'   => ($d->specialty?->name ?? 'Doctor') . ($d->location ? ' · ' . $d->location->name : ''),
                'url'   => route('doctor.show', ['idslug' => $d->seo_slug]),
                'type'  => 'doctor',
            ]);

        $brands = Brand::where('name', 'like', "%$q%")
            ->take(4)->get()
            ->map(fn($b) => [
                'label' => $b->name,
                'sub'   => $b->dosage_form . ' · ' . $b->company,
                'url'   => route('medicine.show', ['id' => $b->id, 'slug' => $b->slug]),
                'type'  => 'medicine',
            ]);

        return response()->json($doctors->merge($brands)->values());
    }

    /**
     * Smart combined suggest: "Cancer Surgeon in Rangpur" style suggestions.
     * Matches if query hits a specialty name, location name, or both.
     */
    public function suggestCombined(Request $request)
    {
        $q = trim(strtolower($request->get('q', '')));
        if (strlen($q) < 2) return response()->json([]);

        // Handle common medical spelling errors
        $synonyms = [
            'gue' => 'gyn', 'gynae' => 'gyne', 'geneco' => 'gyneco', 'gneco' => 'gyneco',
            'ortho' => 'orthop', 'derma' => 'dermat', 'pedia' => 'pediat', 'medcine' => 'medicine'
        ];
        foreach ($synonyms as $bad => $good) {
            if (str_contains($q, $bad)) $q = str_replace($bad, $good, $q);
        }

        // Check if query contains " in " pattern
        $specQ = $q;
        $locQ  = null;
        if (str_contains($q, ' in ')) {
            [$specQ, $locQ] = array_map('trim', explode(' in ', $q, 2));
        }

        $results = [];

        // 1. Specialty-matched results
        $matchedSpecs = Specialty::where('name', 'like', "%$specQ%")->take(4)->get();

        if ($locQ) {
            // User typed "X in Y"
            $matchedLocs = Location::where('name', 'like', "%$locQ%")->take(6)->get();
            if ($matchedSpecs->isNotEmpty() && $matchedLocs->isNotEmpty()) {
                $docCounts = \Illuminate\Support\Facades\DB::table('doctors')
                    ->select('specialty_id', 'location_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                    ->whereIn('specialty_id', $matchedSpecs->pluck('id'))
                    ->whereIn('location_id', $matchedLocs->pluck('id'))
                    ->groupBy('specialty_id', 'location_id')
                    ->get()
                    ->keyBy(fn($i) => $i->specialty_id . '_' . $i->location_id);

                foreach ($matchedSpecs as $spec) {
                    foreach ($matchedLocs as $loc) {
                        $count = $docCounts->get($spec->id . '_' . $loc->id)?->total ?? 0;
                        if ($count > 0) {
                            $results[] = [
                                'label'        => $spec->name . ' in ' . $loc->name,
                                'sub'          => $count . ' doctors available',
                                'url'          => \App\Helpers\SeoHelper::getSeoUrl($spec->id, $loc->id),
                                'type'         => 'combo'
                            ];
                        }
                    }
                }
            }
        } else {
            // No " in " typed. Match Specialty against ALL distinct locations globally mapped.
            if ($matchedSpecs->isNotEmpty()) {
                $docCounts = \Illuminate\Support\Facades\DB::table('doctors')
                    ->select('specialty_id', 'location_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                    ->whereIn('specialty_id', $matchedSpecs->pluck('id'))
                    ->groupBy('specialty_id', 'location_id')
                    ->get();
                
                $validLocIds = $docCounts->pluck('location_id')->unique()->toArray();
                $allLocs = Location::whereIn('id', $validLocIds)->orderBy('name')->get()->keyBy('id');
                $lookup = $docCounts->keyBy(fn($i) => $i->specialty_id . '_' . $i->location_id);

                foreach ($matchedSpecs as $spec) {
                    $added = 0;
                    foreach ($allLocs as $loc) {
                        $count = $lookup->get($spec->id . '_' . $loc->id)?->total ?? 0;
                        if ($count > 0) {
                            $results[] = [
                                'label'        => $spec->name . ' in ' . $loc->name,
                                'sub'          => $count . ' doctors available',
                                'url'          => \App\Helpers\SeoHelper::getSeoUrl($spec->id, $loc->id),
                                'type'         => 'combo'
                            ];
                        }
                        if (count($results) >= 150) break 2;
                    }
                }
            }

            // Also: if query matches a Location name, show ALL combinations for that Location
            $matchedLoc = Location::where('name', 'like', "%$q%")->first();
            if ($matchedLoc && empty($matchedSpecs->count())) {
                 $docCountsLoc = \Illuminate\Support\Facades\DB::table('doctors')
                    ->select('specialty_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                    ->where('location_id', $matchedLoc->id)
                    ->groupBy('specialty_id')
                    ->get();
                    
                 if ($docCountsLoc->isNotEmpty()) {
                     $specialtiesInLoc = Specialty::whereIn('id', $docCountsLoc->pluck('specialty_id'))->orderBy('name')->get()->keyBy('id');
                     foreach ($docCountsLoc as $row) {
                         $spec = $specialtiesInLoc->get($row->specialty_id);
                         if ($spec) {
                             $results[] = [
                                 'label'        => $spec->name . ' in ' . $matchedLoc->name,
                                 'sub'          => $row->total . ' doctors available',
                                 'url'          => \App\Helpers\SeoHelper::getSeoUrl($spec->id, $matchedLoc->id),
                                 'type'         => 'combo'
                             ];
                         }
                         if (count($results) >= 150) break;
                     }
                 }
            }
        }

        // 3. Fallback: also match individual doctors by name
        $doctors = Doctor::with(['specialty','location'])
            ->where('name', 'like', "%$q%")
            ->take(4)->get()
            ->map(fn($d) => [
                'label' => $d->name,
                'sub'   => ($d->specialty?->name ?? '') . ($d->location ? ' · ' . $d->location->name : ''),
                'url'   => route('doctor.show', ['idslug' => $d->seo_slug]),
                'type'  => 'doctor',
            ])->toArray();

        return response()->json(array_slice(array_merge($results, $doctors), 0, 150));
    }

    /**
     * /api/quick-links  — cached 6 hours
     * Returns up to 80 specialty×location combos that have ≥1 doctor.
     */
    public function quickLinks()
    {
        $links = Cache::remember('quick_links', 60 * 360, function () {
            $specialties = Specialty::orderBy('name')->get();
            $locations   = Location::orderBy('name')->get();

            $rows = [];
            foreach ($specialties as $spec) {
                foreach ($locations as $loc) {
                    $cnt = Doctor::where('specialty_id', $spec->id)
                                 ->where('location_id',  $loc->id)
                                 ->count();
                    if ($cnt > 0) {
                        $rows[] = [
                            'label' => $spec->name . ' in ' . $loc->name,
                            'count' => $cnt . ' doctors',
                            'url'   => \App\Helpers\SeoHelper::getSeoUrl($spec->id, $loc->id),
                        ];
                    }
                }
            }

            // Shuffle and return a manageable subset
            return collect($rows)->shuffle()->take(80)->values()->all();
        });

        return response()->json($links);
    }

    /**
     * Map clean SEO path to specialty and location IDs without DB hits (via Cache),
     * and route the user to standard searchDoctors view logic.
     */
    public function handleSeoUrl(Request $request, $seo_path)
    {
        // If a user submitted a filter form (which attaches ?location_id=... etc) while ALREADY on an SEO URL,
        // we must instantly map those explicit form values to their true SEO URL and 301 redirect them,
        // stripping the raw query parameters from the address bar.
        if (!$request->filled('q') && ($request->filled('location_id') || $request->filled('specialty_id'))) {
            $url = \App\Helpers\SeoHelper::getSeoUrl($request->specialty_id, $request->location_id);
            return redirect($url, 301);
        }

        $specialtySlug = null;
        $locationSlug = null;

        if (preg_match('/^best-doctors-in-(.+)$/', $seo_path, $matches)) {
            $locationSlug = $matches[1];
        } elseif (preg_match('/^best-(.+)-doctors-in-bangladesh$/', $seo_path, $matches)) {
            $specialtySlug = $matches[1];
        } elseif (preg_match('/^best-(.+)-doctors-in-(.+)$/', $seo_path, $matches)) {
            $specialtySlug = $matches[1];
            $locationSlug = $matches[2];
        } else {
            abort(404);
        }

        $specialtyId = null;
        $locationId = null;

        if ($specialtySlug) {
            $specialtyId = Cache::rememberForever("map_specialty_slug_{$specialtySlug}", function () use ($specialtySlug) {
                return Specialty::all()->first(fn($s) => \Illuminate\Support\Str::slug($s->name) === $specialtySlug)?->id;
            });
            if (!$specialtyId) abort(404);
        }

        if ($locationSlug) {
            $locationId = Cache::rememberForever("map_location_slug_{$locationSlug}", function () use ($locationSlug) {
                return Location::all()->first(fn($l) => \Illuminate\Support\Str::slug($l->name) === $locationSlug)?->id;
            });
            if (!$locationId) abort(404);
        }

        // Merge IDs into the request so searchDoctors() treats it like a normal query
        $request->merge([
            'specialty_id' => $specialtyId,
            'location_id'  => $locationId,
        ]);

        return $this->searchDoctors($request);
    }
}