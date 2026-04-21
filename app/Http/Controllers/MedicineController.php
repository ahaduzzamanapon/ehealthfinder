<?php
namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Generic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MedicineController extends Controller
{
    public function show($id, $slug = null, $lang = null)
    {
        // Detect /bn suffix via route defaults
        $isBangla = (request()->route()->parameter('lang') === 'bn') || ($lang === 'bn');

        $brand = null;

        if ($slug) {
            $brand = Brand::with('generic')->where('slug', $slug)->first();
        }

        // Fallback to finding by ID if slug not found or missing
        if (!$brand) {
            $brand = Brand::with('generic')->findOrFail($id);
        }

        // SEO Canonical Redirect: Ensure both ID and slug strictly match the canonical URL
        if ($slug !== $brand->slug || (int)$id !== (int)$brand->id) {
            $routeName = $isBangla ? 'medicine.show.bn' : 'medicine.show';
            return redirect()->route($routeName, ['id' => $brand->id, 'slug' => $brand->slug], 301);
        }

        // Find alternatives with same generic
        $alternatives = [];
        if ($brand->generic_id) {
            $alternatives = Brand::where('generic_id', $brand->generic_id)
                                 ->where('id', '!=', $brand->id)
                                 ->take(10)->get();
        }

        return view('medicines.show', compact('brand', 'alternatives', 'isBangla'));
    }

    /** A-Z HTML index of all medicines — one page per letter */
    public function links(Request $request)
    {
        $letter = strtoupper($request->get('letter', 'A'));
        $letter = preg_match('/^[A-Z]$/', $letter) ? $letter : 'A';

        // All unique first letters for the alphabet nav
        $letters = Cache::remember('med_letters', 3600, function () {
            return Brand::selectRaw('UPPER(LEFT(name,1)) as letter')
                ->distinct()
                ->orderByRaw('UPPER(LEFT(name,1))')
                ->pluck('letter')
                ->filter(fn($l) => preg_match('/^[A-Z]$/', $l))
                ->values();
        });

        // All medicines starting with selected letter, paginated
        $medicines = Brand::select('id','name','slug','dosage_form','company','price','strength')
            ->whereRaw('UPPER(LEFT(name,1)) = ?', [$letter])
            ->orderBy('name')
            ->paginate(120)
            ->withQueryString();

        $totalCount = Cache::remember('med_total_count', 3600, fn() => Brand::count());

        return view('medicines.links', compact('medicines', 'letter', 'letters', 'totalCount'));
    }
}