<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ShapeFileController;
use App\Http\Controllers\DistrictMapController;
use App\Http\Controllers\EpiProxyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});
Route::get('/dashboard2', function () {
    return view('dashboard2');
});

Route::get('/dashboard-v2', function () {
    return view('dashboard_v2');
});

Route::get('/about-us', function () {
    $name = "GIS";
    return view('aboutus');
});

Route::get('/contact-us/{mobile}', function ($mobile) {
    return view('contact', compact('mobile'));
});

Route::get('/user/create', [UserController::class, 'create']);
Route::post('/user/store', [UserController::class, 'store'])->name('user.store');

Route::post('/shapefile/upload', [ShapeFileController::class, 'upload'])->name('shapefile.upload');
Route::get('/shapefile/current', [ShapeFileController::class, 'current'])->name('shapefile.current');
Route::delete('/shapefile', [ShapeFileController::class, 'destroy'])->name('shapefile.destroy');

// Individual district JSON maps
// Each district can have its own JSON file stored at storage/app/public/district-maps/
// The UUID is extracted from the JSON content and matched to boundary polygons by name.
Route::get('/district-map',          [DistrictMapController::class, 'index'])->name('district-map.index');
Route::post('/district-map/upload',  [DistrictMapController::class, 'upload'])->name('district-map.upload');
Route::delete('/district-map',       [DistrictMapController::class, 'destroy'])->name('district-map.destroy');

// EPI Tracker proxy (avoids browser CORS by proxying server-side)
// The auth token lives in EpiProxyController::AUTH_TOKEN — never sent to browser.
Route::get('/epi/analytics', [EpiProxyController::class, 'analytics'])->name('epi.analytics');
Route::get('/epi/health',    [EpiProxyController::class, 'health'])->name('epi.health');

// One-time setup so storage/app/public is served at /storage/*:
//   php artisan storage:link
//
// If your default php.ini upload limits are small (default is often 2MB),
// bump these for the ~5-6MB compressed shape file:
//   upload_max_filesize = 20M
//   post_max_size = 20M
