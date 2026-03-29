<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/robots.txt',  [SitemapController::class, 'robots']);

Route::get('/', [SearchController::class, 'home'])->name('home');
Route::get('/doctors', [SearchController::class, 'searchDoctors'])->name('doctors.index');
Route::get('/medicines', [SearchController::class, 'searchMedicines'])->name('medicines.index');

Route::get('/doctor/{idslug}', [DoctorController::class, 'show'])->name('doctor.show');
Route::get('/medicine/{id}/{slug?}', [MedicineController::class, 'show'])->name('medicine.show');

// Autocomplete API
Route::get('/api/suggest/doctors',  [SearchController::class, 'suggestDoctors'])->name('api.suggest.doctors');
Route::get('/api/suggest/medicines',[SearchController::class, 'suggestMedicines'])->name('api.suggest.medicines');
Route::get('/api/suggest/all',      [SearchController::class, 'suggestAll'])->name('api.suggest.all');
Route::get('/api/suggest/combined', [SearchController::class, 'suggestCombined'])->name('api.suggest.combined');
