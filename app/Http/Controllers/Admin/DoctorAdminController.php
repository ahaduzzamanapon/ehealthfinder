<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $doctors = Doctor::with('specialty', 'location')
            ->when($q, fn($query) => $query->where('name', 'like', "%$q%"))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.doctors.index', compact('doctors', 'q'));
    }

    public function create()
    {
        $locations  = Location::orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.doctors.form', compact('locations', 'specialties'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:500',
            'degrees'      => 'nullable|string',
            'experience'   => 'nullable|string',
            'designation'  => 'nullable|string',
            'workplace'    => 'nullable|string',
            'about_text'   => 'nullable|string',
            'location_id'  => 'nullable|integer',
            'specialty_id' => 'nullable|integer',
            'image_path'   => 'nullable|string',
        ]);

        $doctor = Doctor::create($data);
        return redirect()->route('admin.doctors.index')
            ->with('success', "Doctor \"{$doctor->name}\" added successfully.");
    }

    public function edit(Doctor $doctor)
    {
        $locations   = Location::orderBy('name')->get();
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.doctors.form', compact('doctor', 'locations', 'specialties'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:500',
            'degrees'      => 'nullable|string',
            'experience'   => 'nullable|string',
            'designation'  => 'nullable|string',
            'workplace'    => 'nullable|string',
            'about_text'   => 'nullable|string',
            'location_id'  => 'nullable|integer',
            'specialty_id' => 'nullable|integer',
            'image_path'   => 'nullable|string',
        ]);

        $doctor->update($data);
        return redirect()->route('admin.doctors.index')
            ->with('success', "Doctor \"{$doctor->name}\" updated.");
    }

    public function destroy(Doctor $doctor)
    {
        $name = $doctor->name;
        $doctor->delete();
        return redirect()->route('admin.doctors.index')
            ->with('success', "Doctor \"$name\" deleted.");
    }
}
