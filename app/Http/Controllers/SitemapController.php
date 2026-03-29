<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Brand;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index()
    {
        $doctors  = Doctor::select('id', 'name', 'updated_at')->get();
        $medicines = Brand::select('id', 'name', 'slug', 'updated_at')->get();

        $content = view('sitemap', compact('doctors', 'medicines'))->render();

        return response($content, 200)->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $content = "User-agent: *\nAllow: /\n\nSitemap: " . url('/sitemap.xml') . "\n";
        return response($content, 200)->header('Content-Type', 'text/plain');
    }
}
