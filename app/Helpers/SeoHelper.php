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

        $specialty_name = null;
        $location_name  = null;

        if ($specialty_id) {
            $specialty_name = Cache::rememberForever("specialty_name_{$specialty_id}", function () use ($specialty_id) {
                return Specialty::find($specialty_id)?->name;
            });
        }

        if ($location_id) {
            $location_name = Cache::rememberForever("location_name_{$location_id}", function () use ($location_id) {
                return Location::find($location_id)?->name;
            });
        }

        if ($specialty_name && $location_name) {
            return url('/best-' . Str::slug($specialty_name) . '-doctors-in-' . Str::slug($location_name));
        } elseif ($specialty_name) {
            return url('/best-' . Str::slug($specialty_name) . '-doctors-in-bangladesh');
        } elseif ($location_name) {
            return url('/best-doctors-in-' . Str::slug($location_name));
        }

        return route('doctors.index');
    }
}
