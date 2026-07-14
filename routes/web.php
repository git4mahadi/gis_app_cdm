<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ShapeFileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
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

// One-time setup so storage/app/public is served at /storage/*:
//   php artisan storage:link
//
// If your default php.ini upload limits are small (default is often 2MB),
// bump these for the ~5-6MB compressed shape file:
//   upload_max_filesize = 20M
//   post_max_size = 20M
