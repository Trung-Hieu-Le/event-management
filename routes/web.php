<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Auth;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('home');
Route::get('/', [EventController::class, 'index'])->name('events.index');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::post('/events/{event}/favorite', [EventController::class, 'addToFavorites'])->name('events.favorite');
    Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
    Route::post('/events/{id}/update', [EventController::class, 'updateDayOnly'])->name('events.update_day_only');
    Route::post('/events/update', [EventController::class, 'update'])->name('events.update');
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});
