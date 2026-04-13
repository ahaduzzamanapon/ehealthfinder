<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicineApiController extends Controller
{
    public function index(Request $request)
    {
        $q       = $request->input('q', '');
        $perPage = min((int) $request->input('per_page', 20), 50);

        $query = Brand::with('generic')
            ->select('id', 'name', 'bangla_name', 'dosage_form', 'strength',
                     'company', 'price', 'image_path', 'generic_id', 'is_antibiotic');

        if ($q) {
            $query->where(function ($q2) use ($q) {
                $q2->where('name', 'like', "%{$q}%")
                   ->orWhere('bangla_name', 'like', "%{$q}%")
                   ->orWhere('company', 'like', "%{$q}%")
                   ->orWhereHas('generic', fn($g) => $g->where('name', 'like', "%{$q}%"));
            });
        }

        $meds = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $meds->map(fn($m) => $this->formatMini($m)),
            'meta' => [
                'current_page' => $meds->currentPage(),
                'last_page'    => $meds->lastPage(),
                'total'        => $meds->total(),
                'per_page'     => $meds->perPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $brand = Brand::with('generic')->findOrFail($id);
        $alternatives = collect([]);
        if ($brand->generic_id) {
            $alternatives = Brand::where('generic_id', $brand->generic_id)
                ->where('id', '!=', $brand->id)
                ->select('id', 'name', 'dosage_form', 'company', 'price', 'image_path')
                ->take(10)->get()
                ->map(fn($a) => $this->formatMini($a));
        }

        return response()->json([
            'data' => [
                'id'               => $brand->id,
                'name'             => $brand->name,
                'bangla_name'      => $brand->bangla_name,
                'dosage_form'      => $brand->dosage_form,
                'strength'         => $brand->strength,
                'company'          => $brand->company,
                'generic'          => $brand->generic?->name,
                'price'            => $brand->price,
                'is_antibiotic'    => (bool) $brand->is_antibiotic,
                'image_url'        => $brand->image_path ? asset($brand->image_path) : null,
                'slug'             => $brand->slug,
                'url'              => route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug]),
                'url_bn'           => route('medicine.show.bn', ['id' => $brand->id, 'slug' => $brand->slug]),
                'sections' => [
                    'indications'       => ['en' => $brand->indications_en,       'bn' => $brand->indications_bn],
                    'pharmacology'      => ['en' => $brand->mode_of_action_en,    'bn' => $brand->mode_of_action_bn],
                    'dosage'            => ['en' => $brand->dosage_en,            'bn' => $brand->dosage_bn],
                    'interaction'       => ['en' => $brand->interaction_en,       'bn' => $brand->interaction_bn],
                    'contraindications' => ['en' => $brand->contraindications_en, 'bn' => $brand->contraindications_bn],
                    'side_effects'      => ['en' => $brand->side_effects_en,      'bn' => $brand->side_effects_bn],
                    'pregnancy'         => ['en' => $brand->pregnancy_cat_en,     'bn' => $brand->pregnancy_cat_bn],
                    'precautions'       => ['en' => $brand->precautions_en,       'bn' => $brand->precautions_bn],
                    'pediatric'         => ['en' => $brand->pediatric_uses_en,    'bn' => $brand->pediatric_uses_bn],
                    'storage'           => ['en' => $brand->storage_conditions_en,'bn' => $brand->storage_conditions_bn],
                ],
                'rating'           => $brand->averageRating,
                'review_count'     => $brand->reviewCount,
                'alternatives'     => $alternatives,
            ],
        ]);
    }

    private function formatMini($m): array
    {
        return [
            'id'          => $m->id,
            'name'        => $m->name,
            'bangla_name' => $m->bangla_name,
            'dosage_form' => $m->dosage_form,
            'strength'    => $m->strength,
            'company'     => $m->company,
            'generic'     => $m->generic?->name,
            'price'       => $m->price,
            'is_antibiotic' => (bool) $m->is_antibiotic,
            'image_url'   => $m->image_path ? asset($m->image_path) : null,
            'slug'        => $m->slug,
            'url'         => route('medicine.show', ['id' => $m->id, 'slug' => $m->slug]),
        ];
    }
}
