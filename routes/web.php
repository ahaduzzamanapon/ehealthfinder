<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaticPageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DoctorAdminController;
use App\Http\Controllers\Admin\SpecialtyAdminController;
use App\Http\Controllers\Admin\LocationAdminController;
use App\Http\Controllers\Admin\MedicineAdminController;
use App\Http\Controllers\Admin\GenericAdminController;
use App\Http\Controllers\Admin\ChamberAdminController;
use App\Http\Controllers\Admin\BlogCategoryAdminController;
use App\Http\Controllers\Admin\BlogPostAdminController;
use App\Http\Controllers\Admin\BrandScrapeController;
// ── ADMIN ROUTES ─────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    // Login (no auth needed)
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Doctors CRUD
        Route::get('/doctors',            [DoctorAdminController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/create',     [DoctorAdminController::class, 'create'])->name('doctors.create');
        Route::post('/doctors',           [DoctorAdminController::class, 'store'])->name('doctors.store');
        Route::get('/doctors/{doctor}/edit',   [DoctorAdminController::class, 'edit'])->name('doctors.edit');
        Route::put('/doctors/{doctor}',        [DoctorAdminController::class, 'update'])->name('doctors.update');
        Route::delete('/doctors/{doctor}',     [DoctorAdminController::class, 'destroy'])->name('doctors.destroy');

        // Specialties CRUD
        Route::get('/specialties',             [SpecialtyAdminController::class, 'index'])->name('specialties.index');
        Route::post('/specialties',            [SpecialtyAdminController::class, 'store'])->name('specialties.store');
        Route::put('/specialties/{specialty}', [SpecialtyAdminController::class, 'update'])->name('specialties.update');
        Route::delete('/specialties/{specialty}', [SpecialtyAdminController::class, 'destroy'])->name('specialties.destroy');

        // Locations CRUD
        Route::get('/locations',             [LocationAdminController::class, 'index'])->name('locations.index');
        Route::post('/locations',            [LocationAdminController::class, 'store'])->name('locations.store');
        Route::put('/locations/{location}',  [LocationAdminController::class, 'update'])->name('locations.update');
        Route::delete('/locations/{location}', [LocationAdminController::class, 'destroy'])->name('locations.destroy');

        // Medicines CRUD
        Route::get('/medicines',             [MedicineAdminController::class, 'index'])->name('medicines.index');
        Route::post('/medicines/import',     [MedicineAdminController::class, 'importJson'])->name('medicines.import');
        Route::post('/medicines',            [MedicineAdminController::class, 'store'])->name('medicines.store');
        Route::get('/medicines/{medicine}/edit',  [MedicineAdminController::class, 'edit'])->name('medicines.edit');
        Route::put('/medicines/{medicine}',       [MedicineAdminController::class, 'update'])->name('medicines.update');
        Route::delete('/medicines/{medicine}',    [MedicineAdminController::class, 'destroy'])->name('medicines.destroy');

        // Generics CRUD
        Route::post('/generics',             [GenericAdminController::class, 'store'])->name('generics.store');
        Route::delete('/generics/{generic}', [GenericAdminController::class, 'destroy'])->name('generics.destroy');

        // Chambers CRUD (doctor's chambers)
        Route::get('/chambers/create',      [ChamberAdminController::class, 'create'])->name('chambers.create');
        Route::post('/chambers',            [ChamberAdminController::class, 'store'])->name('chambers.store');
        Route::get('/chambers/{chamber}/edit',   [ChamberAdminController::class, 'edit'])->name('chambers.edit');
        Route::put('/chambers/{chamber}',        [ChamberAdminController::class, 'update'])->name('chambers.update');
        Route::delete('/chambers/{chamber}',     [ChamberAdminController::class, 'destroy'])->name('chambers.destroy');

        // Blog Categories
        Route::get('/blog/categories',             [BlogCategoryAdminController::class, 'index'])->name('blog.categories.index');
        Route::post('/blog/categories',            [BlogCategoryAdminController::class, 'store'])->name('blog.categories.store');
        Route::put('/blog/categories/{category}',  [BlogCategoryAdminController::class, 'update'])->name('blog.categories.update');
        Route::delete('/blog/categories/{category}', [BlogCategoryAdminController::class, 'destroy'])->name('blog.categories.destroy');

        // Blog Posts
        Route::get('/blog/posts',                [BlogPostAdminController::class, 'index'])->name('blog.posts.index');
        Route::get('/blog/posts/create',         [BlogPostAdminController::class, 'create'])->name('blog.posts.create');
        Route::post('/blog/posts',               [BlogPostAdminController::class, 'store'])->name('blog.posts.store');
        Route::get('/blog/posts/{post}/edit',    [BlogPostAdminController::class, 'edit'])->name('blog.posts.edit');
        Route::put('/blog/posts/{post}',         [BlogPostAdminController::class, 'update'])->name('blog.posts.update');
        Route::delete('/blog/posts/{post}',      [BlogPostAdminController::class, 'destroy'])->name('blog.posts.destroy');
    });
});


// ── CRON: Brand scraper (protected by token) ─────────────
// Every 10 minutes: curl "https://yoursite.com/cron/scrape-brand?token=YOUR_SECRET"
Route::get('/cron/scrape-brand',   [BrandScrapeController::class, 'scrapeOne'])->name('cron.scrape');
Route::get('/cron/scrape-progress',[BrandScrapeController::class, 'progress'])->name('cron.progress');

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

// Reviews
Route::post('/reviews',    [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');


// Autocomplete API
Route::get('/api/suggest/doctors',  [SearchController::class, 'suggestDoctors'])->name('api.suggest.doctors');
Route::get('/api/suggest/medicines',[SearchController::class, 'suggestMedicines'])->name('api.suggest.medicines');
Route::get('/api/suggest/all',      [SearchController::class, 'suggestAll'])->name('api.suggest.all');
Route::get('/api/suggest/combined', [SearchController::class, 'suggestCombined'])->name('api.suggest.combined');
Route::get('/api/quick-links',      [SearchController::class, 'quickLinks'])->name('api.quick-links');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');

// 10. Dynamic SEO Slug Catch-All (Put this at the VERY END to avoid conflicts)
Route::get('/{seo_path}', [SearchController::class, 'handleSeoUrl'])
    ->where('seo_path', '^(best-.*|doctors-in-.*)$')
    ->name('seo.url');

// 11. Root-level Blog Single Post URL (Must sit exactly at the bottom below dynamic SEO URL)
Route::get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
