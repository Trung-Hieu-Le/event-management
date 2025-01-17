<?php

use App\Http\Controllers\Auth\ProfileController;
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
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
Route::post('/events/update', [EventController::class, 'update'])->name('events.update');

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::post('/events/{event}/favorite', [EventController::class, 'addToFavorites'])->name('events.favorite');
    Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
    Route::post('/events/{id}/update', [EventController::class, 'updateDayOnly'])->name('events.update_day_only');
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changePasswordForm'])->name('profile.change-password');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password.save');
});
