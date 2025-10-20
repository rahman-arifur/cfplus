<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\ContestController;
use App\Http\Controllers\ProblemController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/link-cf', [ProfileController::class, 'linkCf'])->name('profile.link-cf');
    
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/contests', [ContestController::class, 'index'])->name('contests.index');
    Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
});

require __DIR__.'/auth.php';
