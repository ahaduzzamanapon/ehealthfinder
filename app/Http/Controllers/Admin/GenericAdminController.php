<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Generic;
use Illuminate\Http\Request;

class GenericAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:generics,name']);
        Generic::create($data);
        return back()->with('success', "Generic \"{$data['name']}\" added.");
    }

    public function destroy(Generic $generic)
    {
        $generic->delete();
        return back()->with('success', 'Generic deleted.');
    }
}
