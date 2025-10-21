<?php

namespace App\Jobs;

use App\Models\Contest;
use App\Services\CodeforcesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncContestsFromCf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;
    public $backoff = [30, 60, 120];

    /**
     * Execute the job.
     */
    public function handle(CodeforcesApiService $cfApi): void
    {
        try {
            $contestsData = $cfApi->getContests();

            if (empty($contestsData)) {
                Log::warning('SyncContestsFromCf: No contests data received');
                return;
            }

            // Only sync contests from the last 6 months and all upcoming/running
            $sixMonthsAgo = now()->subMonths(6)->timestamp;
            $syncedCount = 0;
            $skippedCount = 0;

            foreach ($contestsData as $contestData) {
                $startTime = $contestData['startTimeSeconds'] ?? 0;
                $phase = $contestData['phase'] ?? 'FINISHED';
                
                // Skip old finished contests
                if ($phase === 'FINISHED' && $startTime < $sixMonthsAgo) {
                    $skippedCount++;
                    continue;
                }

                Contest::updateOrCreate(
                    ['contest_id' => $contestData['id']],
                    [
                        'name' => $contestData['name'],
                        'type' => $contestData['type'] ?? 'CF',
                        'phase' => $phase,
                        'frozen' => $contestData['frozen'] ?? false,
                        'duration_seconds' => $contestData['durationSeconds'] ?? 0,
                        'start_time' => isset($contestData['startTimeSeconds']) 
                            ? date('Y-m-d H:i:s', $contestData['startTimeSeconds'])
                            : null,
                        'relative_time' => isset($contestData['relativeTimeSeconds']) 
                            ? date('Y-m-d H:i:s', time() + $contestData['relativeTimeSeconds'])
                            : null,
                        'description' => $contestData['description'] ?? null,
                        'difficulty' => $contestData['difficulty'] ?? null,
                        'kind' => $contestData['kind'] ?? null,
                        'icpc_region' => $contestData['icpcRegion'] ?? null,
                        'country' => $contestData['country'] ?? null,
                        'city' => $contestData['city'] ?? null,
                        'season' => $contestData['season'] ?? null,
                    ]
                );
                
                $syncedCount++;
            }

            Log::info('SyncContestsFromCf completed', [
                'total' => count($contestsData),
                'synced' => $syncedCount,
                'skipped' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('SyncContestsFromCf failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SyncContestsFromCf job failed permanently', [
            'error' => $exception->getMessage(),
        ]);
    }
}
