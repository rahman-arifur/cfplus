<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class ProblemsController extends Controller
{
    /**
     * Display a listing of problems with filters.
     */
    public function index(Request $request): View
    {

        
        $user = $request->user();
        $query = Problem::query();

        // Filter by user status (solved/attempted/unsolved)
        $statusFilter = $request->get('status_filter');
        if ($user && $statusFilter) {
            if ($statusFilter === 'solved') {
                // Only problems user has solved
                $query->whereHas('users', function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'solved');
                });
            } elseif ($statusFilter === 'attempted') {
                // Only problems user attempted but not solved
                $query->whereHas('users', function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('status', 'attempted');
                });
            } elseif ($statusFilter === 'unsolved') {
                // Problems user has NOT attempted
                $query->whereDoesntHave('users', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
        }

        // Filter by rating range
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        if ($request->filled('max_rating')) {
            $query->where('rating', '<=', $request->max_rating);
        }

        // Filter by tags
        if ($request->filled('tags') && is_array($request->tags)) {
            $query->withTags($request->tags);
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Order by
        $sortBy = $request->get('sort_by', 'rating');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['rating', 'solved_count', 'code'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate results
        $problems = $query->paginate(50)->withQueryString();

        // Load user's problem status for display
        if ($user) {
            $userProblemIds = $user->problems()->pluck('problems.id')->toArray();
            $userSolvedIds = $user->solvedProblems()->pluck('problems.id')->toArray();
            
            foreach ($problems as $problem) {
                $problem->user_status = null;
                if (in_array($problem->id, $userSolvedIds)) {
                    $problem->user_status = 'solved';
                } elseif (in_array($problem->id, $userProblemIds)) {
                    $problem->user_status = 'attempted';
                }
            }
        }

        // Get all unique tags for the filter dropdown
        $allTags = $this->getAllTags();

        // Get user stats for display
        $userStats = null;
        if ($user) {
            $userStats = [
                'solved_count' => $user->solvedProblems()->count(),
                'attempted_count' => $user->attemptedProblems()->count(),
                'total_count' => Problem::count(),
            ];
        }

        return view('problems.index', [
            'problems' => $problems,
            'allTags' => $allTags,
            'userStats' => $userStats,
            'filters' => $request->only(['min_rating', 'max_rating', 'tags', 'search', 'sort_by', 'sort_order', 'status_filter']),
        ]);
    }

    /**
     * Get a random problem based on filters.
     */
    public function random(Request $request)
    {
        $user = $request->user();
        $query = Problem::query();

        // Filter by user status (solved/attempted/unsolved)
        $statusFilter = $request->get('status_filter');
        if ($user && $statusFilter) {
            if ($statusFilter === 'solved') {
                $query->whereHas('users', function($q) use ($user) {
                    $q->where('user_id', $user->id)->where('status', 'solved');
                });
            } elseif ($statusFilter === 'attempted') {
                $query->whereHas('users', function($q) use ($user) {
                    $q->where('user_id', $user->id)->where('status', 'attempted');
                });
            } elseif ($statusFilter === 'unsolved') {
                $query->whereDoesntHave('users', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }
        }

        // Apply same filters as index
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        if ($request->filled('max_rating')) {
            $query->where('rating', '<=', $request->max_rating);
        }

        if ($request->filled('tags') && is_array($request->tags)) {
            $query->withTags($request->tags);
        }

        // Get random problem
        $problem = $query->inRandomOrder()->first();

        if (!$problem) {
            return back()->with('error', 'No problems found matching your filters.');
        }

        // Redirect to Codeforces problem page
        return redirect()->away($problem->url);
    }

    /**
     * Get all unique tags from problems.
     */
    protected function getAllTags(): array
    {
        $problems = Problem::whereNotNull('tags')->get(['tags']);
        $allTags = [];

        foreach ($problems as $problem) {
            if ($problem->tags && is_array($problem->tags)) {
                $allTags = array_merge($allTags, $problem->tags);
            }
        }

        $allTags = array_unique($allTags);
        sort($allTags);

        return $allTags;
    }
}
