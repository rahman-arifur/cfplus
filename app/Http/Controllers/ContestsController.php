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
        $participatedContestsData = [];
        if ($user = $request->user()) {
            $participatedContestsData = $this->getUserParticipatedContests($user);
        }

        return view('contests.index', [
            'contests' => $contests,
            'filter' => $filter,
            'participatedContestsData' => $participatedContestsData,
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
     * Get contest data for contests that user has participated in.
     */
    protected function getUserParticipatedContests($user): array
    {
        $cfAccount = $user->cfAccount;
        
        if (!$cfAccount) {
            return [];
        }

        try {
            // Get all rating snapshots for the user
            $snapshots = $cfAccount->ratingSnapshots()
                ->get()
                ->keyBy('contest_id');

            // Get user submissions to calculate solve count per contest
            $cfApi = app(CodeforcesApiService::class);
            $submissions = $cfApi->getUserStatus($cfAccount->handle, 1000);

            $contestSolveCount = [];
            
            if (!empty($submissions)) {
                foreach ($submissions as $submission) {
                    $verdict = $submission['verdict'] ?? null;
                    $contestId = $submission['problem']['contestId'] ?? null;
                    $problemIndex = $submission['problem']['index'] ?? null;

                    // Only count accepted solutions
                    if ($verdict === 'OK' && $contestId && $problemIndex) {
                        $key = $contestId . '_' . $problemIndex;
                        
                        if (!isset($contestSolveCount[$contestId])) {
                            $contestSolveCount[$contestId] = [];
                        }
                        
                        // Track unique solved problems per contest
                        $contestSolveCount[$contestId][$key] = true;
                    }
                }
            }

            // Build the participated contests data
            $participatedData = [];
            foreach ($snapshots as $contestId => $snapshot) {
                $participatedData[$contestId] = [
                    'rating_change' => $snapshot->rating_change,
                    'old_rating' => $snapshot->old_rating,
                    'new_rating' => $snapshot->new_rating,
                    'rank' => $snapshot->rank,
                    'solve_count' => isset($contestSolveCount[$contestId]) ? count($contestSolveCount[$contestId]) : 0,
                ];
            }

            return $participatedData;

        } catch (\Exception $e) {
            Log::error('Failed to get user participated contests', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return [];
        }
    }
}
