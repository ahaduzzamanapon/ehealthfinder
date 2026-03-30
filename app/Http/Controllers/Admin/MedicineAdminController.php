<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Generic;
use Illuminate\Http\Request;

class MedicineAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $medicines = Brand::with('generic')
            ->when($q, fn($query) => $query->where('name', 'like', "%$q%")
                ->orWhere('company', 'like', "%$q%"))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $generics = Generic::orderBy('name')->get();
        return view('admin.medicines.index', compact('medicines', 'q', 'generics'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:500',
            'dosage_form'  => 'nullable|string|max:255',
            'generic_id'   => 'nullable|integer',
            'company'      => 'nullable|string|max:500',
            'price'        => 'nullable|string|max:100',
            'is_antibiotic'=> 'nullable|boolean',
        ]);
        $data['is_antibiotic'] = $request->boolean('is_antibiotic');
        Brand::create($data);
        return back()->with('success', "Medicine \"{$data['name']}\" added.");
    }

    public function edit(Brand $medicine)
    {
        $generics = Generic::orderBy('name')->get();
        return view('admin.medicines.edit', compact('medicine', 'generics'));
    }

    public function update(Request $request, Brand $medicine)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:500',
            'dosage_form'  => 'nullable|string|max:255',
            'generic_id'   => 'nullable|integer',
            'company'      => 'nullable|string|max:500',
            'price'        => 'nullable|string|max:100',
            'is_antibiotic'=> 'nullable|boolean',
        ]);
        $data['is_antibiotic'] = $request->boolean('is_antibiotic');
        $medicine->update($data);
        return redirect()->route('admin.medicines.index')
            ->with('success', "Medicine \"{$medicine->name}\" updated.");
    }

    public function destroy(Brand $medicine)
    {
        $name = $medicine->name;
        $medicine->delete();
        return back()->with('success', "Medicine \"$name\" deleted.");
    }
}
