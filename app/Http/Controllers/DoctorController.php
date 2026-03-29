<?php
namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function show($idslug)
    {
        // Parse "123-dr-some-name" → id=123, slug=dr-some-name
        if (preg_match('/^(\d+)-(.+)$/', $idslug, $m)) {
            $id   = $m[1];
            $slug = $m[2];
        } else {
            $id   = $idslug;
            $slug = null;
        }

        $doctor = Doctor::with(['location', 'specialty', 'chambers.hospital', 'chambers.hospital.location'])->findOrFail($id);

        // Canonical redirect if slug is missing or wrong
        $canonical = $doctor->id . '-' . Str::slug($doctor->name);
        if ($idslug !== $canonical) {
            return redirect()->route('doctor.show', ['idslug' => $canonical], 301);
        }

        return view('doctors.show', compact('doctor'));
    }
}