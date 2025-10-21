<?php

namespace App\Http\Controllers;

use App\Models\UserContest;
use App\Models\Problem;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserContestsController extends Controller
{
    /**
     * Display user's custom contests.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $contests = $user->userContests()
            ->with('problems')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Prepare performance chart data for completed contests
        $completedContests = $user->userContests()
            ->with('problems')
            ->where('status', 'completed')
            ->orderBy('completed_at', 'asc')
            ->get()
            ->map(function($contest) {
                $solved = $contest->problems->where('pivot.solved_during_contest', true)->count();
                $total = $contest->problems->count();
                $avgRating = $contest->problems->avg('rating') ?? 0;
                
                return [
                    'title' => $contest->title,
                    'completed_at' => $contest->completed_at?->format('M d'),
                    'progress' => $contest->progress,
                    'avg_rating' => round($avgRating),
                    'solved' => $solved,
                    'total' => $total,
                ];
            });

        return view('user-contests.index', compact('contests', 'completedContests'));
    }

    /**
     * Show the form for creating a new contest.
     */
    public function create(): View
    {
        // Get all unique tags for filter dropdown
        $allTags = $this->getAllTags();

        return view('user-contests.create', compact('allTags'));
    }

    /**
     * Store a newly created contest.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'rating_min' => 'nullable|integer|min:800|max:3500',
            'rating_max' => 'nullable|integer|min:800|max:3500|gte:rating_min',
            'problem_count' => 'required|integer|min:1|max:10',
            'duration_minutes' => 'required|integer|min:30|max:300',
            'tags' => 'nullable|string',
            'action' => 'nullable|string|in:create_draft,create_and_start',
        ]);

        // Parse tags if provided
        $tags = null;
        if (!empty($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
            $tags = array_filter($tags);
        }

        // Generate problems for the contest
        $problemsData = array_merge($validated, ['tags' => $tags]);
        $problems = $this->generateContestProblems($user, $problemsData);

        if ($problems->isEmpty()) {
            return back()->withErrors(['general' => 'No problems found matching your criteria. Try adjusting the filters.']);
        }

        if ($problems->count() < $validated['problem_count']) {
            return back()->withErrors(['general' => "Only {$problems->count()} problems found, but you requested {$validated['problem_count']}. Try widening your criteria."]);
        }

        // Create the contest
        $contestData = $validated;
        $contestData['tags'] = $tags;
        unset($contestData['action']); // Remove action from contest data
        
        $contest = $user->userContests()->create($contestData);

        // Attach problems to contest
        $contest->problems()->attach($problems->pluck('id'));

        // Handle action - start immediately or save as draft
        if ($request->input('action') === 'create_and_start') {
            $contest->start();
            return redirect()->route('user-contests.participate', $contest)
                ->with('success', 'Contest created and started! Good luck!');
        }

        return redirect()->route('user-contests.show', $contest)
            ->with('success', 'Contest created successfully! Click "Start Contest" when ready.');
    }

    /**
     * Display the specified contest.
     */
    public function show(Request $request, UserContest $userContest): View
    {
        // Make sure user owns this contest
        abort_unless($userContest->user_id === $request->user()->id, 403);

        // Load problems with pivot data
        $userContest->load(['problems' => function ($query) {
            $query->withPivot(['solved_during_contest', 'solved_at']);
        }]);

        return view('user-contests.show', ['contest' => $userContest]);
    }

    /**
     * Start the contest.
     */
    public function start(Request $request, UserContest $userContest): RedirectResponse
    {
        if ($userContest->user_id !== $request->user()->id) {
            abort(403);
        }

        if (!$userContest->isDraft()) {
            return back()->withErrors(['general' => 'Contest has already been started.']);
        }

        $userContest->start();

        return redirect()->route('user-contests.participate', $userContest)
            ->with('success', 'Contest started! Good luck!');
    }

    /**
     * Show contest participation interface.
     */
    public function participate(Request $request, UserContest $userContest)
    {
        // Check authorization
        if ($userContest->user_id !== $request->user()->id) {
            abort(403);
        }

        // Check if contest is draft - redirect to show page instead
        if ($userContest->isDraft()) {
            return redirect()->route('user-contests.show', $userContest)
                ->with('info', 'Contest is still in draft mode. Start it to begin participating.');
        }

        // Check if contest is completed
        if ($userContest->isCompleted()) {
            return redirect()->route('user-contests.show', $userContest)
                ->with('info', 'This contest has been completed.');
        }

        // Check if contest is active
        if (!$userContest->isActive()) {
            return redirect()->route('user-contests.show', $userContest)
                ->with('error', 'Contest is not active.');
        }

        // Eager load problems with pivot data for better performance
        $userContest->load(['problems' => function ($query) {
            $query->orderBy('rating');
        }]);

        return view('user-contests.participate', compact('userContest'));
    }

    /**
     * Update problem solve status during contest.
     */
    public function updateProblemStatus(Request $request, UserContest $userContest, Problem $problem): RedirectResponse|JsonResponse
    {
        Log::info('updateProblemStatus called', [
            'user_contest_id' => $userContest->id,
            'problem_id' => $problem->id,
            'request_data' => $request->all(),
            'is_ajax' => $request->wantsJson()
        ]);
        
        // Check user owns this contest
        if ($userContest->user_id !== $request->user()->id) {
            abort(403);
        }

        if (!$userContest->isActive()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Contest is not active.'], 400);
            }
            return back()->withErrors(['general' => 'Contest is not active.']);
        }

        $validated = $request->validate([
            'solved' => 'required|in:0,1',
        ]);

        $solvedStatus = (bool) $validated['solved'];

        // Check if problem is part of this contest
        $contestProblem = $userContest->problems()->where('problems.id', $problem->id)->first();
        
        if (!$contestProblem) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Problem not found in this contest.'], 404);
            }
            return back()->withErrors(['general' => 'Problem not found in this contest.']);
        }

        // Update solve status
        $userContest->problems()->updateExistingPivot($problem->id, [
            'solved_during_contest' => $solvedStatus,
            'solved_at' => $solvedStatus ? now() : null,
        ]);

        $message = $solvedStatus ? 'Problem marked as solved!' : 'Problem marked as unsolved.';
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => $message,
                'solved' => $solvedStatus
            ]);
        }
        
        return back()->with('success', $message);
    }

    /**
     * Complete the contest manually.
     */
    public function complete(Request $request, UserContest $userContest): RedirectResponse
    {
        // Check user owns this contest
        if ($userContest->user_id !== $request->user()->id) {
            abort(403);
        }

        if (!$userContest->isActive()) {
            return back()->withErrors(['general' => 'Contest is not active.']);
        }

        $userContest->complete();

        return redirect()->route('user-contests.show', $userContest)
            ->with('success', 'Contest completed!');
    }

    /**
     * Generate problems for contest based on criteria.
     */
    protected function generateContestProblems($user, array $criteria)
    {
        $query = Problem::query();

        // Filter by rating range
        if (!empty($criteria['rating_min'])) {
            $query->where('rating', '>=', $criteria['rating_min']);
        }
        if (!empty($criteria['rating_max'])) {
            $query->where('rating', '<=', $criteria['rating_max']);
        }

        // Filter by tags
        if (!empty($criteria['tags'])) {
            $query->withTags($criteria['tags']);
        }

        // Exclude problems user has already solved or attempted
        $query->whereDoesntHave('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Get random problems
        return $query->inRandomOrder()
            ->limit($criteria['problem_count'])
            ->get();
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
