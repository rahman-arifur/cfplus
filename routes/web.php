<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\ContestsController;
use App\Http\Controllers\UserContestsController;
use App\Http\Controllers\ProblemsController;
use App\Http\Controllers\DashboardController;
use App\Services\CodeforcesApiService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Temporary test route for Codeforces API
Route::get('/test-cf-api', function (CodeforcesApiService $cfApi) {
    $handle = 'tourist'; // Test with a known CF handle
    
    $userInfo = $cfApi->getUserInfo($handle);
    $rating = $cfApi->getUserRating($handle);
    $status = $cfApi->getUserStatus($handle, 10);
    
    return response()->json([
        'handle' => $handle,
        'user_info' => $userInfo,
        'rating_count' => count($rating),
        'submissions_count' => count($status),
        'latest_rating' => !empty($rating) ? end($rating) : null,
    ]);
})->name('test.cf.api');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/link-cf', [ProfileController::class, 'linkCf'])->name('profile.link-cf');
    Route::post('/profile/sync-cf', [ProfileController::class, 'syncCf'])->name('profile.sync-cf');
    
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/contests', [ContestsController::class, 'index'])->name('contests.index');
    Route::get('/problems', [ProblemsController::class, 'index'])->name('problems.index');
    Route::get('/problems/random', [ProblemsController::class, 'random'])->name('problems.random');
    
    // User Custom Contests
    Route::prefix('custom-contests')->name('user-contests.')->group(function () {
        Route::get('/', [UserContestsController::class, 'index'])->name('index');
        Route::get('/create', [UserContestsController::class, 'create'])->name('create');
        Route::post('/', [UserContestsController::class, 'store'])->name('store');
        Route::get('/{userContest}', [UserContestsController::class, 'show'])->name('show');
        Route::post('/{userContest}/start', [UserContestsController::class, 'start'])->name('start');
        Route::get('/{userContest}/participate', [UserContestsController::class, 'participate'])->name('participate');
        Route::post('/{userContest}/problem/{problem}/status', [UserContestsController::class, 'updateProblemStatus'])->name('update-problem-status');
        Route::post('/{userContest}/sync-status', [UserContestsController::class, 'syncStatus'])->name('sync-status');
        Route::post('/{userContest}/complete', [UserContestsController::class, 'complete'])->name('complete');
    });
});

require __DIR__.'/auth.php';
