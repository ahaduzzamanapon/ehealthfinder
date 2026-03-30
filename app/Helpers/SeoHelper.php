<?php

namespace App\Helpers;

use App\Models\Specialty;
use App\Models\Location;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SeoHelper
{
    /**
     * Generate SEO-friendly deep links for doctor search
     */
    public static function getSeoUrl($specialty_id = null, $location_id = null)
    {
        if (!$specialty_id && !$location_id) {
            return route('doctors.index');
        }

        $specialty = null;
        $location = null;

        if ($specialty_id) {
            $specialties = Cache::rememberForever('map_specialties', fn() => Specialty::all());
            $specialty = $specialties->firstWhere('id', $specialty_id);
        }

        if ($location_id) {
            $locations = Cache::rememberForever('map_locations', fn() => Location::all());
            $location = $locations->firstWhere('id', $location_id);
        }

        if ($specialty && $location) {
            return url('/best-' . Str::slug($specialty->name) . '-doctors-in-' . Str::slug($location->name));
        } elseif ($specialty) {
            return url('/best-' . Str::slug($specialty->name) . '-doctors-in-bangladesh');
        } elseif ($location) {
            return url('/best-doctors-in-' . Str::slug($location->name));
        }

        return route('doctors.index');
    }
}
