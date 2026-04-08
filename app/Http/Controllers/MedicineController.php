<?php
namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Generic;

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
}