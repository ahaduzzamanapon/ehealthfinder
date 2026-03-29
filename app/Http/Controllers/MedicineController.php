<?php
namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Generic;

class MedicineController extends Controller
{
    public function show($id, $slug = null)
    {
        $brand = Brand::with('generic')->findOrFail($id);
        
        // SEO Redirect
        if ($slug !== $brand->slug) {
            return redirect()->route('medicine.show', ['id' => $brand->id, 'slug' => $brand->slug], 301);
        }
        
        // Find alternatives with same generic
        $alternatives = [];
        if ($brand->generic_id) {
            $alternatives = Brand::where('generic_id', $brand->generic_id)
                                 ->where('id', '!=', $brand->id)
                                 ->take(10)->get();
        }
        
        return view('medicines.show', compact('brand', 'alternatives'));
    }
}