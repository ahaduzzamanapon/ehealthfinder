<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Specialty;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Cache;

class HomeApiController extends Controller
{
    public function index()
    {
        $data = Cache::remember('api_home_data', 3600, function () {

            $medicineCount = Brand::count();
            $doctorCount   = Doctor::count();
            $locationCount = Location::count();
            $specialtyCount= Specialty::count();

            $featuredMeds = Brand::with('generic')
                ->whereNotNull('image_path')
                ->whereNotNull('indications_en')
                ->select('id', 'name', 'dosage_form', 'company', 'price', 'image_path', 'generic_id')
                ->inRandomOrder()
                ->take(8)->get()
                ->map(fn($m) => [
                    'id'          => $m->id,
                    'name'        => $m->name,
                    'dosage_form' => $m->dosage_form,
                    'company'     => $m->company,
                    'price'       => $m->price,
                    'generic'     => $m->generic?->name,
                    'image_url'   => $m->image_path ? asset($m->image_path) : null,
                    'slug'        => $m->slug,
                    'url'         => route('medicine.show', ['id' => $m->id, 'slug' => $m->slug]),
                ]);

            $featuredDocs = Doctor::with(['specialty', 'location'])
                ->whereNotNull('image_path')
                ->select('id', 'name', 'degrees', 'specialty_id', 'location_id',
                         'workplace', 'image_path')
                ->inRandomOrder()
                ->take(6)->get()
                ->map(fn($d) => [
                    'id'        => $d->id,
                    'name'      => $d->name,
                    'degrees'   => $d->degrees,
                    'specialty' => $d->specialty?->name,
                    'location'  => $d->location?->name,
                    'workplace' => $d->workplace,
                    'image_url' => $d->image_path ? asset($d->image_path) : null,
                    'url'       => route('doctor.show', ['idslug' => $d->seo_slug]),
                ]);

            $locations = Location::orderBy('name')
                ->select('id', 'name')->get()
                ->map(fn($l) => ['id' => $l->id, 'name' => $l->name]);

            $specialties = Specialty::orderBy('name')
                ->select('id', 'name')->get()
                ->map(fn($s) => ['id' => $s->id, 'name' => $s->name]);

            return [
                'stats' => [
                    'medicines'   => $medicineCount,
                    'doctors'     => $doctorCount,
                    'locations'   => $locationCount,
                    'specialties' => $specialtyCount,
                ],
                'featured_medicines' => $featuredMeds,
                'featured_doctors'   => $featuredDocs,
                'locations'          => $locations,
                'specialties'        => $specialties,
            ];
        });

        return response()->json(['data' => $data]);
    }
}
