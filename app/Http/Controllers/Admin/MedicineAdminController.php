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

    public function importJson(Request $request)
    {
        // Disable PHP execution time limit for massive datasets
        set_time_limit(0);

        $request->validate([
            'json_file' => 'required|file|mimetypes:application/json,text/plain'
        ]);

        $file = $request->file('json_file');
        $content = file_get_contents($file->getRealPath());
        $json = json_decode($content, true);

        if (!$json) {
            return back()->with('error', 'Invalid JSON file format.');
        }

        $genericsCount = 0;
        $brandsCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($json as $block) {
                if (($block['type'] ?? '') === 'table' && isset($block['data']) && is_array($block['data'])) {
                    
                    if ($block['name'] === 'g' || $block['name'] === 'generics') {
                        foreach ($block['data'] as $g) {
                            $existing = \App\Models\Generic::where('name', $g['name'] ?? '')->first();
                            if (!$existing && !empty($g['name'])) {
                                \App\Models\Generic::create([
                                    'id' => $g['id'] ?? null, // Will respect manual ID insert if DB allows, else it auto-increments
                                    'name' => $g['name'],
                                    'indication' => $g['indication'] ?? null,
                                    'pharmacology' => $g['pharmacology'] ?? null,
                                    'dosage' => $g['dosage'] ?? null,
                                    'interaction' => $g['interaction'] ?? null,
                                    'contraindications' => $g['contraindications'] ?? null,
                                    'side_effects' => $g['side_effects'] ?? null,
                                    'pregnancy_cat' => $g['pregnancy_cat'] ?? null,
                                    'precautions' => $g['precautions'] ?? null,
                                    'therapeutic_class' => $g['therapeutic_class'] ?? null,
                                    'mode_of_action' => $g['mode_of_action'] ?? null,
                                ]);
                                $genericsCount++;
                            }
                        }
                    }

                    if ($block['name'] === 'b' || $block['name'] === 'brands') {
                        foreach ($block['data'] as $b) {
                            $name = $b['name'] ?? 'Unknown';
                            $slug = \Illuminate\Support\Str::slug($name . ' ' . ($b['dosage_form'] ?? ''));
                            
                            // Prevent DB unique constraint failure if slug exists by appending uniqueness
                            while (\App\Models\Brand::where('slug', $slug)->exists()) {
                                $slug = \Illuminate\Support\Str::slug($name . ' ' . ($b['dosage_form'] ?? '')) . '-' . \Illuminate\Support\Str::random(5);
                            }

                            // Auto-create or map generic by generic_name
                            $genericId = null;
                            if (!empty($b['generic_name'])) {
                                $genName = trim($b['generic_name']);
                                $realGeneric = \App\Models\Generic::firstOrCreate(
                                    ['name' => $genName]
                                );
                                $genericId = $realGeneric->id;
                            }
                            // Fallback if not found by name
                            if (!$genericId && !empty($b['generic_id'])) {
                                $genericId = $b['generic_id'];
                            }

                            \App\Models\Brand::create([
                                'name' => $name,
                                'slug' => $slug,
                                'medex_id' => $b['medex_id'] ?? null,
                                'dosage_form' => $b['dosage_form'] ?? null,
                                'generic_id' => $genericId,
                                'company' => $b['company'] ?? null,
                                'price' => $b['price'] ?? null,
                                'is_antibiotic' => !empty($b['is_antibiotic']),
                                'image_path' => $b['image_path'] ?? null,
                                'indications_en' => $b['indications_en'] ?? null,
                                'indications_bn' => $b['indications_bn'] ?? null,
                                'dosage_en' => $b['dosage_en'] ?? null,
                                'dosage_bn' => $b['dosage_bn'] ?? null,
                                'interaction_en' => $b['interaction_en'] ?? null,
                                'interaction_bn' => $b['interaction_bn'] ?? null,
                                'contraindications_en' => $b['contraindications_en'] ?? null,
                                'contraindications_bn' => $b['contraindications_bn'] ?? null,
                                'side_effects_en' => $b['side_effects_en'] ?? null,
                                'side_effects_bn' => $b['side_effects_bn'] ?? null,
                                'pregnancy_cat_en' => $b['pregnancy_cat_en'] ?? null,
                                'pregnancy_cat_bn' => $b['pregnancy_cat_bn'] ?? null,
                                'precautions_en' => $b['precautions_en'] ?? null,
                                'precautions_bn' => $b['precautions_bn'] ?? null,
                                'mode_of_action_en' => $b['mode_of_action_en'] ?? null,
                                'mode_of_action_bn' => $b['mode_of_action_bn'] ?? null,
                            ]);
                            $brandsCount++;
                        }
                    }
                }
            }
            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', "JSON Import complete! Inserted $genericsCount new generics and $brandsCount new brands.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->with('error', 'Error importing JSON: ' . $e->getMessage());
        }
    }
}
