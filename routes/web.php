<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\ContestsController;
use App\Http\Controllers\ProblemController;
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

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/link-cf', [ProfileController::class, 'linkCf'])->name('profile.link-cf');
    Route::post('/profile/sync-cf', [ProfileController::class, 'syncCf'])->name('profile.sync-cf');
    
    Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    Route::get('/contests', [ContestsController::class, 'index'])->name('contests.index');
    Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
});

require __DIR__.'/auth.php';
