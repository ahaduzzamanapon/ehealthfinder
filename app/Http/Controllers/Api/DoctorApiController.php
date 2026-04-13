<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Specialty;
use Illuminate\Http\Request;

class DoctorApiController extends Controller
{
    public function index(Request $request)
    {
        $q          = $request->input('q', '');
        $locationId = $request->input('location_id');
        $specialtyId= $request->input('specialty_id');
        $perPage    = min((int) $request->input('per_page', 20), 50);

        $query = Doctor::with(['specialty', 'location', 'chambers.hospital'])
            ->select('id', 'name', 'degrees', 'specialty_id', 'location_id',
                     'workplace', 'designation', 'experience', 'image_path');

        if ($q) {
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('degrees', 'like', "%{$q}%")
                   ->orWhere('workplace', 'like', "%{$q}%")
                   ->orWhereHas('specialty', fn($s) => $s->where('name', 'like', "%{$q}%"));
            });
        }
        if ($locationId) $query->where('location_id', $locationId);
        if ($specialtyId) $query->where('specialty_id', $specialtyId);

        $docs = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $docs->map(fn($d) => $this->formatMini($d)),
            'meta' => [
                'current_page' => $docs->currentPage(),
                'last_page'    => $docs->lastPage(),
                'total'        => $docs->total(),
                'per_page'     => $docs->perPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $doctor = Doctor::with([
            'specialty', 'location',
            'chambers.hospital', 'chambers.hospital.location'
        ])->findOrFail($id);

        return response()->json([
            'data' => [
                'id'          => $doctor->id,
                'name'        => $doctor->name,
                'degrees'     => $doctor->degrees,
                'designation' => $doctor->designation,
                'workplace'   => $doctor->workplace,
                'experience'  => $doctor->experience,
                'about'       => $doctor->about_text,
                'specialty'   => $doctor->specialty?->name,
                'location'    => $doctor->location?->name,
                'image_url'   => $doctor->image_path ? asset($doctor->image_path) : null,
                'url'         => route('doctor.show', ['idslug' => $doctor->seo_slug]),
                'rating'      => $doctor->averageRating ?? 0,
                'review_count'=> $doctor->reviewCount ?? 0,
                'chambers'    => $doctor->chambers->map(fn($c) => [
                    'id'                 => $c->id,
                    'hospital'           => $c->hospital?->name ?? 'Private Chamber',
                    'hospital_location'  => $c->hospital?->location?->name,
                    'address'            => $c->address,
                    'visiting_hour'      => $c->visiting_hour,
                    'appointment_number' => $c->appointment_number,
                ]),
            ],
        ]);
    }

    private function formatMini($d): array
    {
        $firstChamber = $d->chambers->first();
        return [
            'id'          => $d->id,
            'name'        => $d->name,
            'degrees'     => $d->degrees,
            'designation' => $d->designation,
            'workplace'   => $d->workplace,
            'experience'  => $d->experience,
            'specialty'   => $d->specialty?->name,
            'location'    => $d->location?->name,
            'image_url'   => $d->image_path ? asset($d->image_path) : null,
            'url'         => route('doctor.show', ['idslug' => $d->seo_slug]),
            'appointment_number' => $firstChamber?->appointment_number,
            'hospital'    => $firstChamber?->hospital?->name,
        ];
    }
}
