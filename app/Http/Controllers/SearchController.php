<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
                'url'      => route('doctor.show', ['id' => $d->id, 'slug' => \Illuminate\Support\Str::slug($d->name)]),
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
                'url'   => route('doctor.show', ['id' => $d->id, 'slug' => \Illuminate\Support\Str::slug($d->name)]),
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
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) return response()->json([]);

        // Check if query contains " in " pattern — e.g. "heart in dhaka"
        $specQ = $q;
        $locQ  = null;
        if (stripos($q, ' in ') !== false) {
            [$specQ, $locQ] = array_map('trim', explode(' in ', strtolower($q), 2));
        }

        $results = [];

        // 1. Specialty-matched results (cross all locations)
        $matchedSpecs = Specialty::where('name', 'like', "%$specQ%")->take(4)->get();

        if ($locQ) {
            // User typed "X in Y" — match specialty x location combos
            $matchedLocs = Location::where('name', 'like', "%$locQ%")->take(6)->get();
            foreach ($matchedSpecs as $spec) {
                foreach ($matchedLocs as $loc) {
                    $count = Doctor::where('specialty_id', $spec->id)->where('location_id', $loc->id)->count();
                    if ($count > 0) {
                        $results[] = [
                            'label'        => $spec->name . ' in ' . $loc->name,
                            'sub'          => $count . ' doctors available',
                            'url'          => route('doctors.index', ['specialty_id' => $spec->id, 'location_id' => $loc->id]),
                            'type'         => 'combo',
                            'specialty_id' => $spec->id,
                            'location_id'  => $loc->id,
                        ];
                    }
                }
            }
        } else {
            // No " in " — match specialty against all locations
            $allLocs = Location::orderBy('name')->take(8)->get();
            foreach ($matchedSpecs as $spec) {
                foreach ($allLocs as $loc) {
                    $count = Doctor::where('specialty_id', $spec->id)->where('location_id', $loc->id)->count();
                    if ($count > 0) {
                        $results[] = [
                            'label'        => $spec->name . ' in ' . $loc->name,
                            'sub'          => $count . ' doctors available',
                            'url'          => route('doctors.index', ['specialty_id' => $spec->id, 'location_id' => $loc->id]),
                            'type'         => 'combo',
                            'specialty_id' => $spec->id,
                            'location_id'  => $loc->id,
                        ];
                    }
                }
                // Limit to 6 combo results per specialty
                if (count($results) >= 8) break;
            }

            // Also: if query matches a location name, show all specialties for that location
            $matchedLoc = Location::where('name', 'like', "%$q%")->first();
            if ($matchedLoc && empty($matchedSpecs->count())) {
                $specsInLoc = Specialty::whereHas('doctors', fn($dq) => $dq->where('location_id', $matchedLoc->id))
                    ->orderBy('name')->take(8)->get();
                foreach ($specsInLoc as $spec) {
                    $count = Doctor::where('specialty_id', $spec->id)->where('location_id', $matchedLoc->id)->count();
                    $results[] = [
                        'label'        => $spec->name . ' in ' . $matchedLoc->name,
                        'sub'          => $count . ' doctors available',
                        'url'          => route('doctors.index', ['specialty_id' => $spec->id, 'location_id' => $matchedLoc->id]),
                        'type'         => 'combo',
                        'specialty_id' => $spec->id,
                        'location_id'  => $matchedLoc->id,
                    ];
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
                'url'   => route('doctor.show', ['id' => $d->id, 'slug' => \Illuminate\Support\Str::slug($d->name)]),
                'type'  => 'doctor',
            ])->toArray();

        return response()->json(array_slice(array_merge($results, $doctors), 0, 10));
    }
}