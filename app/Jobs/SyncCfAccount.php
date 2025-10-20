<?php

namespace App\Jobs;

use App\Models\CfAccount;
use App\Models\RatingSnapshot;
use App\Services\CodeforcesApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncCfAccount implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * The CfAccount instance.
     */
    protected CfAccount $cfAccount;

    /**
     * Create a new job instance.
     */
    public function __construct(CfAccount $cfAccount)
    {
        $this->cfAccount = $cfAccount;
    }

    /**
     * Execute the job.
     */
    public function handle(CodeforcesApiService $cfApi): void
    {
        $handle = $this->cfAccount->handle;

        Log::info("Starting Codeforces sync for handle: {$handle}");

        try {
            // Fetch user info from Codeforces API
            $userInfo = $cfApi->getUserInfo($handle);

            if (!$userInfo) {
                throw new Exception("Failed to fetch user info for handle: {$handle}");
            }

            // Fetch rating history to get max rating
            $ratingHistory = $cfApi->getUserRating($handle);
            $maxRating = $this->calculateMaxRating($ratingHistory);

            // Update CF account with fetched data
            $this->cfAccount->update([
                'avatar' => $userInfo['avatar'] ?? $userInfo['titlePhoto'] ?? null,
                'country' => $userInfo['country'] ?? null,
                'city' => $userInfo['city'] ?? null,
                'organization' => $userInfo['organization'] ?? null,
                'current_rating' => $userInfo['rating'] ?? null,
                'max_rating' => $maxRating ?? $userInfo['maxRating'] ?? null,
                'last_synced_at' => now(),
            ]);

            // Store rating history in rating_snapshots
            $this->syncRatingHistory($ratingHistory);

            // Sync problem status (solved/attempted)
            $this->syncProblemStatus($cfApi, $handle);

            Log::info("Successfully synced Codeforces account", [
                'handle' => $handle,
                'current_rating' => $this->cfAccount->current_rating,
                'max_rating' => $this->cfAccount->max_rating,
                'rating_snapshots_count' => count($ratingHistory),
            ]);

        } catch (Exception $e) {
            Log::error("Failed to sync Codeforces account", [
                'handle' => $handle,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // If we've exhausted all retries, mark sync as failed but don't crash
            if ($this->attempts() >= $this->tries) {
                Log::error("Exhausted all retry attempts for handle: {$handle}");
                
                // Update last_synced_at to prevent constant retries
                $this->cfAccount->update([
                    'last_synced_at' => now(),
                ]);
            }

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Sync rating history to rating_snapshots table.
     *
     * @param array $ratingHistory
     * @return void
     */
    protected function syncRatingHistory(array $ratingHistory): void
    {
        if (empty($ratingHistory)) {
            Log::info("No rating history to sync for handle: {$this->cfAccount->handle}");
            return;
        }

        foreach ($ratingHistory as $contest) {
            // Use updateOrCreate to avoid duplicates
            RatingSnapshot::updateOrCreate(
                [
                    'cf_account_id' => $this->cfAccount->id,
                    'contest_id' => $contest['contestId'],
                ],
                [
                    'contest_name' => $contest['contestName'] ?? 'Unknown Contest',
                    'rank' => $contest['rank'] ?? null,
                    'old_rating' => $contest['oldRating'],
                    'new_rating' => $contest['newRating'],
                    'rated_at' => isset($contest['ratingUpdateTimeSeconds']) 
                        ? date('Y-m-d H:i:s', $contest['ratingUpdateTimeSeconds'])
                        : now(),
                ]
            );
        }

        Log::info("Synced rating history", [
            'handle' => $this->cfAccount->handle,
            'contests' => count($ratingHistory),
        ]);
    }

    /**
     * Calculate max rating from rating history.
     *
     * @param array $ratingHistory
     * @return int|null
     */
    protected function calculateMaxRating(array $ratingHistory): ?int
    {
        if (empty($ratingHistory)) {
            return null;
        }

        $maxRating = 0;
        foreach ($ratingHistory as $contest) {
            if (isset($contest['newRating']) && $contest['newRating'] > $maxRating) {
                $maxRating = $contest['newRating'];
            }
        }

        return $maxRating > 0 ? $maxRating : null;
    }

    /**
     * Sync user's problem status (solved/attempted) based on submissions.
     *
     * @param CodeforcesApiService $cfApi
     * @param string $handle
     * @return void
     */
    protected function syncProblemStatus(CodeforcesApiService $cfApi, string $handle): void
    {
        try {
            // Fetch user's submissions (we'll get all to be comprehensive)
            $submissions = $cfApi->getUserStatus($handle);

            if (empty($submissions)) {
                Log::info("No submissions found for handle: {$handle}");
                return;
            }

            $problemStats = [];

            // Process submissions to determine problem status
            foreach ($submissions as $submission) {
                $problem = $submission['problem'] ?? null;
                if (!$problem) continue;

                $contestId = $problem['contestId'] ?? null;
                $index = $problem['index'] ?? null;
                if (!$contestId || !$index) continue;

                $code = $contestId . $index;
                $verdict = $submission['verdict'] ?? null;
                $creationTime = $submission['creationTimeSeconds'] ?? null;

                // Initialize problem stats if not exists
                if (!isset($problemStats[$code])) {
                    $problemStats[$code] = [
                        'code' => $code,
                        'status' => 'attempted',
                        'solved_at' => null,
                        'attempts' => 0,
                    ];
                }

                $problemStats[$code]['attempts']++;

                // If this submission was OK, mark as solved
                if ($verdict === 'OK') {
                    $problemStats[$code]['status'] = 'solved';
                    
                    // Track the first time it was solved
                    $solvedAt = $problemStats[$code]['solved_at'];
                    if (!$solvedAt || ($creationTime && $creationTime < strtotime($solvedAt))) {
                        $problemStats[$code]['solved_at'] = $creationTime 
                            ? date('Y-m-d H:i:s', $creationTime)
                            : now()->toDateTimeString();
                    }
                }
            }

            // Sync to database
            $user = $this->cfAccount->user;
            $syncedCount = 0;

            foreach ($problemStats as $code => $stats) {
                // Find the problem in our database
                $problem = \App\Models\Problem::where('code', $code)->first();
                
                if (!$problem) {
                    // Problem not in our database yet, skip it
                    continue;
                }

                // Update or create the pivot record
                $user->problems()->syncWithoutDetaching([
                    $problem->id => [
                        'status' => $stats['status'],
                        'solved_at' => $stats['solved_at'],
                        'attempts' => $stats['attempts'],
                        'updated_at' => now(),
                    ]
                ]);

                $syncedCount++;
            }

            Log::info("Synced problem status", [
                'handle' => $handle,
                'total_submissions' => count($submissions),
                'unique_problems' => count($problemStats),
                'synced_to_db' => $syncedCount,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to sync problem status", [
                'handle' => $handle,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - let the rating sync complete even if problem sync fails
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Exception $exception): void
    {
        Log::error("SyncCfAccount job failed permanently", [
            'handle' => $this->cfAccount->handle,
            'error' => $exception?->getMessage(),
        ]);
    }
}
