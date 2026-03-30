<?php
namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function show($idslug)
    {
        // Parse "dental-surgeon-in-bogra-dr-name-123" (new) OR "123-dr-name" (legacy)
        if (preg_match('/-(\d+)$/', $idslug, $m)) {
            $id = $m[1];
        } elseif (preg_match('/^(\d+)-/', $idslug, $m)) {
            $id = $m[1];
        } elseif (is_numeric($idslug)) {
            $id = $idslug;
        } else {
            abort(404);
        }

        $doctor = Doctor::with(['location', 'specialty', 'chambers.hospital', 'chambers.hospital.location'])->findOrFail($id);

        // Canonical redirect with new SEO URL structure
        $canonical = $doctor->seo_slug;
        if ($idslug !== $canonical) {
            return redirect()->route('doctor.show', ['idslug' => $canonical], 301);
        }

        return view('doctors.show', compact('doctor'));
    }
}