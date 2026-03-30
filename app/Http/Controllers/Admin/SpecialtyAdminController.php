<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $specialties = Specialty::when($q, fn($query) => $query->where('name', 'like', "%$q%"))
            ->withCount('doctors')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();
        return view('admin.specialties.index', compact('specialties', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:specialties,name']);
        Specialty::create($data);
        return back()->with('success', "Specialty \"{$data['name']}\" added.");
    }

    public function update(Request $request, Specialty $specialty)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:specialties,name,'.$specialty->id]);
        $specialty->update($data);
        return back()->with('success', "Specialty updated.");
    }

    public function destroy(Specialty $specialty)
    {
        if ($specialty->doctors()->count() > 0) {
            return back()->with('error', "Cannot delete — {$specialty->doctors()->count()} doctors linked.");
        }
        $specialty->delete();
        return back()->with('success', "Specialty deleted.");
    }
}
