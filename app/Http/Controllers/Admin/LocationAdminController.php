<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $locations = Location::when($q, fn($query) => $query->where('name', 'like', "%$q%"))
            ->withCount('doctors')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();
        return view('admin.locations.index', compact('locations', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:locations,name']);
        Location::create($data);
        return back()->with('success', "Location \"{$data['name']}\" added.");
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate(['name' => 'required|string|max:255|unique:locations,name,'.$location->id]);
        $location->update($data);
        return back()->with('success', "Location updated.");
    }

    public function destroy(Location $location)
    {
        if ($location->doctors()->count() > 0) {
            return back()->with('error', "Cannot delete — {$location->doctors()->count()} doctors linked.");
        }
        $location->delete();
        return back()->with('success', "Location deleted.");
    }
}
