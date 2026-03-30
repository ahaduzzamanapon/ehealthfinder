<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chamber;
use App\Models\Hospital;
use App\Models\Doctor;
use App\Models\Location;
use Illuminate\Http\Request;

class ChamberAdminController extends Controller
{
    public function create(Request $request)
    {
        $doctor    = Doctor::findOrFail($request->doctor_id);
        $hospitals = Hospital::with('location')->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        return view('admin.chambers.form', compact('doctor', 'hospitals', 'locations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'doctor_id'          => 'required|integer',
            'hospital_id'        => 'nullable|integer',
            'address'            => 'nullable|string',
            'visiting_hour'      => 'nullable|string',
            'appointment_number' => 'nullable|string',
        ]);
        Chamber::create($data);
        return redirect()->route('admin.doctors.edit', $data['doctor_id'])
            ->with('success', 'Chamber added successfully.');
    }

    public function edit(Chamber $chamber)
    {
        $doctor    = $chamber->doctor;
        $hospitals = Hospital::with('location')->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        return view('admin.chambers.form', compact('chamber', 'doctor', 'hospitals', 'locations'));
    }

    public function update(Request $request, Chamber $chamber)
    {
        $data = $request->validate([
            'hospital_id'        => 'nullable|integer',
            'address'            => 'nullable|string',
            'visiting_hour'      => 'nullable|string',
            'appointment_number' => 'nullable|string',
        ]);
        $chamber->update($data);
        return redirect()->route('admin.doctors.edit', $chamber->doctor_id)
            ->with('success', 'Chamber updated.');
    }

    public function destroy(Chamber $chamber)
    {
        $doctorId = $chamber->doctor_id;
        $chamber->delete();
        return redirect()->route('admin.doctors.edit', $doctorId)
            ->with('success', 'Chamber deleted.');
    }
}
