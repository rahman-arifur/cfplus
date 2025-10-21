<?php

namespace App\Jobs;

use App\Models\CfAccount;
use App\Models\Problem;
use App\Services\CodeforcesApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncUserSubmissions implements ShouldQueue
{
    use Queueable;

    protected $cfAccountId;

    /**
     * Create a new job instance.
     */
    public function __construct($cfAccountId)
    {
        $this->cfAccountId = $cfAccountId;
    }

    /**
     * Execute the job.
     */
    public function handle(CodeforcesApiService $apiService): void
    {
        try {
            $cfAccount = CfAccount::find($this->cfAccountId);
            
            if (!$cfAccount) {
                Log::warning("CF Account not found: {$this->cfAccountId}");
                return;
            }

            Log::info("Syncing submissions for CF handle: {$cfAccount->handle}");

            // Fetch user submissions from Codeforces API
            $submissions = $apiService->getUserSubmissions($cfAccount->handle);

            if (!$submissions) {
                Log::warning("No submissions found for {$cfAccount->handle}");
                return;
            }

            // Track solved problems
            $solvedProblems = [];

            foreach ($submissions as $submission) {
                // Only process accepted (OK) submissions
                if ($submission['verdict'] !== 'OK') {
                    continue;
                }

                // Extract problem information
                $problem = $submission['problem'] ?? null;
                
                if (!$problem) {
                    continue;
                }

                $contestId = $problem['contestId'] ?? null;
                $index = $problem['index'] ?? null;

                if (!$contestId || !$index) {
                    continue;
                }

                // Create unique identifier for the problem
                $problemKey = "{$contestId}-{$index}";

                // Add to solved problems (using array to avoid duplicates)
                $solvedProblems[$problemKey] = [
                    'contest_id' => $contestId,
                    'index' => $index,
                    'name' => $problem['name'] ?? 'Unknown',
                    'rating' => $problem['rating'] ?? null,
                    'tags' => $problem['tags'] ?? [], // Store as array, Laravel will cast to JSON
                ];
            }

            Log::info("Found " . count($solvedProblems) . " solved problems for {$cfAccount->handle}");

            // Update or create problem records and mark as solved by this user
            foreach ($solvedProblems as $problemData) {
                $code = $problemData['contest_id'] . $problemData['index'];
                
                $problem = Problem::updateOrCreate(
                    [
                        'code' => $code,
                    ],
                    [
                        'contest_id' => $problemData['contest_id'],
                        'index' => $problemData['index'],
                        'name' => $problemData['name'],
                        'rating' => $problemData['rating'],
                        'tags' => $problemData['tags'],
                    ]
                );

                // You could add a pivot table here to track user-problem solved status
                // For now, we just ensure the problem exists in our database
            }

            Log::info("Successfully synced submissions for {$cfAccount->handle}");
            
        } catch (\Exception $e) {
            Log::error("Error syncing submissions: " . $e->getMessage());
            throw $e;
        }
    }
}
