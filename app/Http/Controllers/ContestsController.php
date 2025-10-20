<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Services\CodeforcesApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ContestsController extends Controller
{
    /**
     * Display a listing of contests.
     */
    public function index(Request $request): View
    {
        // Sync contests from API (cache for 1 hour)
        $lastSync = cache('contests_last_sync');
        if (!$lastSync || $lastSync < now()->subHour()) {
            $this->syncContests();
            cache(['contests_last_sync' => now()], 3600); // Cache for 1 hour
        }

        $filter = $request->get('filter', 'upcoming');

        // Get contests based on filter
        $contests = match($filter) {
            'running' => Contest::running()->get(),
            'past' => Contest::past()->paginate(20),
            default => Contest::upcoming()->get(),
        };

        // Get user's participated contests if logged in
        $participatedContestIds = [];
        if ($user = $request->user()) {
            $participatedContestIds = $this->getUserParticipatedContests($user);
        }

        return view('contests.index', [
            'contests' => $contests,
            'filter' => $filter,
            'participatedContestIds' => $participatedContestIds,
        ]);
    }

    /**
     * Sync contests from Codeforces API.
     */
    protected function syncContests(): void
    {
        try {
            $cfApi = app(CodeforcesApiService::class);
            $contestsData = $cfApi->getContests();

            if (empty($contestsData)) {
                Log::warning('No contests data received from API');
                return;
            }

            // Only sync contests from the last 6 months and all upcoming
            $sixMonthsAgo = now()->subMonths(6)->timestamp;
            $syncedCount = 0;

            foreach ($contestsData as $contestData) {
                $startTime = $contestData['startTimeSeconds'] ?? 0;
                $phase = $contestData['phase'] ?? 'FINISHED';
                
                // Skip old finished contests (but keep upcoming/running)
                if ($phase === 'FINISHED' && $startTime < $sixMonthsAgo) {
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

            Log::info('Contests synced successfully', [
                'total' => count($contestsData),
                'synced' => $syncedCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync contests', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get contest IDs that user has participated in.
     */
    protected function getUserParticipatedContests($user): array
    {
        $cfAccount = $user->cfAccount;
        
        if (!$cfAccount) {
            return [];
        }

        // Get all contest IDs from user's rating snapshots
        return $cfAccount->ratingSnapshots()
            ->pluck('contest_id')
            ->toArray();
    }
}
