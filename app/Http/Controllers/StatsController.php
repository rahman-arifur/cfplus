<?php

namespace App\Http\Controllers;

use App\Services\CodeforcesApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class StatsController extends Controller
{
    /**
     * Display the stats page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $cfAccount = $user->cfAccount;

        // If user doesn't have a CF account linked, redirect to profile
        if (!$cfAccount) {
            return redirect()->route('profile.show')
                ->with('error', 'Please link your Codeforces account first.');
        }

        // Get rating history from snapshots (for the graph)
        $ratingHistory = $cfAccount->ratingSnapshots()
            ->orderBy('rated_at', 'asc')
            ->get()
            ->map(function ($snapshot) {
                return [
                    'contest_name' => $snapshot->contest_name,
                    'rating' => $snapshot->new_rating,
                    'change' => $snapshot->rating_change,
                    'date' => $snapshot->rated_at->format('Y-m-d'),
                    'timestamp' => $snapshot->rated_at->timestamp * 1000, // for Chart.js
                ];
            });

        // Get problem statistics
        $problemStats = $this->getProblemStats($cfAccount->handle);

        return view('stats.index', [
            'cfAccount' => $cfAccount,
            'ratingHistory' => $ratingHistory,
            'problemStats' => $problemStats,
        ]);
    }

    /**
     * Get problem statistics from Codeforces API.
     *
     * @param string $handle
     * @return array
     */
    protected function getProblemStats(string $handle): array
    {
        try {
            $cfApi = app(CodeforcesApiService::class);
            
            // Fetch submissions (500 is a good balance - fast but shows recent patterns)
            $submissions = $cfApi->getUserStatus($handle, 500);

            if (empty($submissions)) {
                Log::warning('No submissions found', ['handle' => $handle]);
                return $this->getEmptyProblemStats();
            }

            // Calculate timestamp for 1 month ago
            $oneMonthAgo = now()->subMonth()->timestamp;

            $solvedProblems = [];
            $attemptedProblems = [];
            $verdictCounts = [];
            $languageCounts = [];
            $tagCounts = [];

            foreach ($submissions as $submission) {
                // Skip submissions older than 1 month
                $submissionTime = $submission['creationTimeSeconds'] ?? 0;
                if ($submissionTime < $oneMonthAgo) {
                    continue;
                }

                // Count by verdict
                $verdict = $submission['verdict'] ?? 'UNKNOWN';
                $verdictCounts[$verdict] = ($verdictCounts[$verdict] ?? 0) + 1;

                // Count by language
                $language = $submission['programmingLanguage'] ?? 'Unknown';
                $languageCounts[$language] = ($languageCounts[$language] ?? 0) + 1;

                // Track solved and attempted problems
                if (isset($submission['problem'])) {
                    $problemKey = $submission['problem']['contestId'] . '-' . $submission['problem']['index'];
                    $attemptedProblems[$problemKey] = true;

                    if ($verdict === 'OK') {
                        $solvedProblems[$problemKey] = true;

                        // Count problem tags (only for solved problems)
                        if (isset($submission['problem']['tags'])) {
                            foreach ($submission['problem']['tags'] as $tag) {
                                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
                            }
                        }
                    }
                }
            }

            // Sort tag counts
            arsort($tagCounts);

            $stats = [
                'total_submissions' => $verdictCounts ? array_sum($verdictCounts) : 0,
                'solved_count' => count($solvedProblems),
                'attempted_count' => count($attemptedProblems),
                'verdict_counts' => $verdictCounts,
                'language_counts' => $languageCounts,
                'tag_counts' => $tagCounts,
            ];

            Log::info('Calculated problem stats (past month)', $stats);

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to fetch problem stats', [
                'handle' => $handle,
                'error' => $e->getMessage(),
            ]);

            return $this->getEmptyProblemStats();
        }
    }

    /**
     * Get empty problem stats structure.
     *
     * @return array
     */
    protected function getEmptyProblemStats(): array
    {
        return [
            'total_submissions' => 0,
            'solved_count' => 0,
            'attempted_count' => 0,
            'verdict_counts' => [],
            'language_counts' => [],
            'tag_counts' => [],
        ];
    }
}
