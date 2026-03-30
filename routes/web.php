<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaticPageController;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap/{type}/{page}.xml', [SitemapController::class, 'show'])->name('sitemap.show');
Route::get('/robots.txt',  [SitemapController::class, 'robots']);

Route::get('/', [SearchController::class, 'home'])->name('home');
Route::get('/doctors', [SearchController::class, 'searchDoctors'])->name('doctors.index');
Route::get('/medicines', [SearchController::class, 'searchMedicines'])->name('medicines.index');

Route::get('/doctor/{idslug}', [DoctorController::class, 'show'])->name('doctor.show');
Route::get('/medicine/{id}/{slug?}', [MedicineController::class, 'show'])->name('medicine.show');

// Static Pages
Route::get('/about',       [StaticPageController::class, 'about'])->name('about');
Route::get('/privacy',     [StaticPageController::class, 'privacy'])->name('privacy');
Route::get('/disclaimer',  [StaticPageController::class, 'disclaimer'])->name('disclaimer');
Route::get('/terms',       [StaticPageController::class, 'terms'])->name('terms');

// Autocomplete API
Route::get('/api/suggest/doctors',  [SearchController::class, 'suggestDoctors'])->name('api.suggest.doctors');
Route::get('/api/suggest/medicines',[SearchController::class, 'suggestMedicines'])->name('api.suggest.medicines');
Route::get('/api/suggest/all',      [SearchController::class, 'suggestAll'])->name('api.suggest.all');
Route::get('/api/suggest/combined', [SearchController::class, 'suggestCombined'])->name('api.suggest.combined');
Route::get('/api/quick-links',      [SearchController::class, 'quickLinks'])->name('api.quick-links');

// 10. Dynamic SEO Slug Catch-All (Put this at the VERY END to avoid conflicts)
Route::get('/{seo_path}', [SearchController::class, 'handleSeoUrl'])
    ->where('seo_path', '^(best-.*|doctors-in-.*)$')
    ->name('seo.url');
