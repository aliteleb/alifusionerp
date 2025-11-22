<?php

use App\Http\Controllers\ManifestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Web App Manifest Route (Subdomain Only)
Route::domain('{subdomain}.'.config('app.domain'))
    ->group(function () {
        Route::get('/site.webmanifest', [ManifestController::class, 'show'])->name('subdomain.manifest');
    });
