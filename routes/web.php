<?php

use App\Http\Controllers\Auth\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
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

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/event/{id}', [EventController::class, 'show'])->name('event.show');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::put('/update-events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/event/invite', [EventController::class, 'inviteUser'])->name('event.invite');
    Route::post('/event/respond-invite', [EventController::class, 'respondInvite'])->name('event.respondInvite');
    Route::get('/event/invitations', [EventController::class, 'getInvitations'])->name('event.invitations');


    Route::post('/task/store', [TaskController::class, 'store'])->name('task.store');
    Route::post('/task/update-status', [TaskController::class, 'updateStatus'])->name('task.updateStatus');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/change-password', [ProfileController::class, 'changePasswordForm'])->name('profile.change-password');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password.save');
});
