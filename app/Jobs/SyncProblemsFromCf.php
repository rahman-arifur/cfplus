<?php

namespace App\Jobs;

use App\Models\Problem;
use App\Services\CodeforcesApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncProblemsFromCf implements ShouldQueue
{
    use Queueable;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(CodeforcesApiService $apiService): void
    {
        try {
            Log::info('Starting problem sync from Codeforces');

            // Get problemset from API
            $data = $apiService->getProblemset();

            if (empty($data['problems'])) {
                Log::warning('No problems data received from API');
                return;
            }

            $problems = $data['problems'];
            $problemStatistics = $data['problemStatistics'] ?? [];

            // Create a map of solved counts
            $solvedCounts = [];
            foreach ($problemStatistics as $stat) {
                $key = $stat['contestId'] . '_' . $stat['index'];
                $solvedCounts[$key] = $stat['solvedCount'] ?? 0;
            }

            $syncedCount = 0;
            $chunkSize = 100;

            // Process in chunks to avoid memory issues
            foreach (array_chunk($problems, $chunkSize) as $problemChunk) {
                foreach ($problemChunk as $problem) {
                    $contestId = $problem['contestId'] ?? null;
                    $index = $problem['index'] ?? null;

                    if (!$contestId || !$index) {
                        continue;
                    }

                    $code = $contestId . $index;
                    $key = $contestId . '_' . $index;

                    Problem::updateOrCreate(
                        ['code' => $code],
                        [
                            'contest_id' => $contestId,
                            'index' => $index,
                            'name' => $problem['name'] ?? 'Unknown',
                            'rating' => $problem['rating'] ?? null,
                            'tags' => $problem['tags'] ?? [],
                            'type' => $problem['type'] ?? 'PROGRAMMING',
                            'solved_count' => $solvedCounts[$key] ?? 0,
                        ]
                    );

                    $syncedCount++;
                }
            }

            Log::info('Problems synced successfully', [
                'total' => count($problems),
                'synced' => $syncedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync problems', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
