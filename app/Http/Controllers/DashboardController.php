<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with comprehensive user statistics.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $cfAccount = $user->cfAccount;

        // 1. Get User Rating Info
        $currentRating = $user->getCurrentRating();
        $peakRating = $user->getPeakRating();
        $ratingTrend = $user->getRatingTrend();
        $rank = $this->getRankFromRating($currentRating);
        
        // Get latest rating change
        $latestContest = $user->userContests()
            ->where('status', 'completed')
            ->whereNotNull('rating_change')
            ->latest('completed_at')
            ->first();
        $latestRatingChange = $latestContest ? $latestContest->rating_change : 0;

        // 2. Quick Stats
        $totalContests = $user->userContests()->where('status', 'completed')->count();
        $activeContests = $user->userContests()->where('status', 'active')->count();
        $draftContests = $user->userContests()->where('status', 'draft')->count();
        
        // Calculate total problems solved across all contests
        $totalProblemsSolved = $user->userContests()
            ->where('status', 'completed')
            ->get()
            ->sum(function($contest) {
                return $contest->problems->where('pivot.solved_during_contest', true)->count();
            });
        
        // Calculate overall accuracy
        $totalProblemsAttempted = $user->userContests()
            ->where('status', 'completed')
            ->get()
            ->sum(function($contest) {
                return $contest->problems->count();
            });
        $accuracy = $totalProblemsAttempted > 0 
            ? round(($totalProblemsSolved / $totalProblemsAttempted) * 100) 
            : 0;
        
        // Calculate streak (days with contest activity)
        $streak = $this->calculateStreak($user);

        // 3. Rating Progress Chart Data (last 10 contests)
        $ratingHistory = $user->userContests()
            ->where('status', 'completed')
            ->whereNotNull('actual_rating')
            ->orderBy('completed_at', 'asc')
            ->limit(10)
            ->get()
            ->map(function($contest) {
                return [
                    'title' => $contest->title,
                    'date' => $contest->completed_at->format('M d'),
                    'actual_rating' => $contest->actual_rating ?? 1500,
                    'performance_rating' => $contest->performance_rating ?? 0,
                    'rating_change' => $contest->rating_change ?? 0,
                ];
            });

        // 4. Recent Activity (last 5 activities)
        $recentActivity = $this->getRecentActivity($user);

        // 5. Quick Actions - just pass the data, actions are in view

        // 7. Upcoming & Active Contests
        $activeContestsList = $user->userContests()
            ->where('status', 'active')
            ->with('problems')
            ->orderBy('started_at', 'desc')
            ->get();
        
        $draftContestsList = $user->userContests()
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('dashboard', compact(
            'user',
            'cfAccount',
            'currentRating',
            'peakRating',
            'ratingTrend',
            'rank',
            'latestRatingChange',
            'totalContests',
            'totalProblemsSolved',
            'accuracy',
            'streak',
            'ratingHistory',
            'recentActivity',
            'activeContestsList',
            'draftContestsList',
            'activeContests',
            'draftContests'
        ));
    }

    /**
     * Get rank name from rating (Codeforces style).
     */
    private function getRankFromRating(int $rating): array
    {
        return match(true) {
            $rating < 1200 => ['name' => 'Newbie', 'color' => 'text-gray-600', 'bg' => 'bg-gray-100', 'emoji' => '👶'],
            $rating < 1400 => ['name' => 'Pupil', 'color' => 'text-green-600', 'bg' => 'bg-green-100', 'emoji' => '👨‍🎓'],
            $rating < 1600 => ['name' => 'Specialist', 'color' => 'text-cyan-600', 'bg' => 'bg-cyan-100', 'emoji' => '⚡'],
            $rating < 1900 => ['name' => 'Expert', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100', 'emoji' => '💎'],
            $rating < 2100 => ['name' => 'Candidate Master', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100', 'emoji' => '🎯'],
            $rating < 2300 => ['name' => 'Master', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100', 'emoji' => '🏆'],
            default => ['name' => 'Grandmaster', 'color' => 'text-red-600', 'bg' => 'bg-red-100', 'emoji' => '👑'],
        };
    }

    /**
     * Calculate user's current streak (consecutive days with activity).
     */
    private function calculateStreak($user): int
    {
        $contestDates = $user->userContests()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->pluck('completed_at')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->values();

        if ($contestDates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $today = Carbon::today();
        $checkDate = $today->copy();

        foreach ($contestDates as $contestDate) {
            $contestCarbon = Carbon::parse($contestDate);
            
            if ($contestCarbon->isSameDay($checkDate) || $contestCarbon->isSameDay($checkDate->subDay())) {
                $streak++;
                $checkDate = $contestCarbon;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get recent activity feed.
     */
    private function getRecentActivity($user): array
    {
        $activities = [];

        // Get recent completed contests
        $recentContests = $user->userContests()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentContests as $contest) {
            $solvedCount = $contest->problems->where('pivot.solved_during_contest', true)->count();
            $activities[] = [
                'type' => 'contest',
                'icon' => '🏆',
                'title' => "Completed \"{$contest->title}\" contest",
                'details' => "Rating: {$contest->actual_rating} (" . ($contest->rating_change >= 0 ? '+' : '') . "{$contest->rating_change}) • Solved: {$solvedCount}/{$contest->problems->count()}",
                'time' => $contest->completed_at,
                'color' => 'text-blue-600',
            ];
        }

        // Add milestone achievements
        if ($user->userContests()->where('status', 'completed')->count() === 1) {
            $firstContest = $user->userContests()->where('status', 'completed')->first();
            if ($firstContest) {
                $activities[] = [
                    'type' => 'achievement',
                    'icon' => '⭐',
                    'title' => 'First Contest Completed!',
                    'details' => 'Started your competitive programming journey',
                    'time' => $firstContest->completed_at,
                    'color' => 'text-yellow-600',
                ];
            }
        }

        // Sort by time and limit to 5
        usort($activities, function($a, $b) {
            return $b['time'] <=> $a['time'];
        });

        return array_slice($activities, 0, 5);
    }
}
