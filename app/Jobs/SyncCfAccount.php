<?php

namespace App\Jobs;

use App\Models\CfAccount;
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

            Log::info("Successfully synced Codeforces account", [
                'handle' => $handle,
                'current_rating' => $this->cfAccount->current_rating,
                'max_rating' => $this->cfAccount->max_rating,
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
