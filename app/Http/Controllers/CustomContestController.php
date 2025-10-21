<?php

namespace App\Http\Controllers;

use App\Models\CustomContest;
use App\Models\CustomContestParticipation;
use App\Models\Problem;
use App\Http\Requests\StoreCustomContestRequest;
use App\Http\Requests\UpdateCustomContestRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CustomContestController extends Controller
{
    /**
     * Display a listing of the user's custom contests.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        $contests = CustomContest::where('user_id', $user->id)
            ->with('problems')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('custom-contests.index', [
            'contests' => $contests,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('custom-contests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomContestRequest $request): RedirectResponse
    {
        $contest = CustomContest::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'start_at' => $request->start_at,
            'duration_minutes' => $request->duration_minutes ?? 120,
            'include_in_stats' => $request->boolean('include_in_stats', false),
            'is_public' => $request->boolean('is_public', false),
            'status' => 'draft',
        ]);

        return redirect()
            ->route('custom-contests.show', $contest)
            ->with('success', 'Contest created successfully! Add problems to get started.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, CustomContest $customContest): View
    {
        // Authorization: only owner can view (for now)
        if ($customContest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $customContest->load('problems', 'participations.user');

        return view('custom-contests.show', [
            'contest' => $customContest,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, CustomContest $customContest): View
    {
        // Authorization: only owner can edit
        if ($customContest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        return view('custom-contests.edit', [
            'contest' => $customContest,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomContestRequest $request, CustomContest $customContest): RedirectResponse
    {
        // Authorization: only owner can update
        if ($customContest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $customContest->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_at' => $request->start_at,
            'duration_minutes' => $request->duration_minutes,
            'include_in_stats' => $request->boolean('include_in_stats'),
            'is_public' => $request->boolean('is_public'),
            'status' => $request->status ?? $customContest->status,
        ]);

        return redirect()
            ->route('custom-contests.show', $customContest)
            ->with('success', 'Contest updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CustomContest $customContest): RedirectResponse
    {
        // Authorization: only owner can delete
        if ($customContest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $customContest->delete();

        return redirect()
            ->route('custom-contests.index')
            ->with('success', 'Contest deleted successfully!');
    }

    /**
     * Search for problems to add to the contest.
     */
    public function searchProblems(Request $request, CustomContest $contest): View|JsonResponse
    {
        // Authorization
        if ($contest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $query = Problem::query();

        // Filter by rating range
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        if ($request->filled('max_rating')) {
            $query->where('rating', '<=', $request->max_rating);
        }

        // Filter by tags
        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', trim($tag));
            }
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Exclude already attached problems
        $attachedIds = $contest->problems->pluck('id')->toArray();
        if (!empty($attachedIds)) {
            $query->whereNotIn('id', $attachedIds);
        }

        $problems = $query->orderBy('rating', 'asc')
            ->orderBy('solved_count', 'desc')
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($problems);
        }

        return view('custom-contests.problems.search', [
            'contest' => $contest,
            'problems' => $problems,
        ]);
    }

    /**
     * Attach a problem to the contest.
     */
    public function attachProblem(Request $request, CustomContest $contest, Problem $problem): RedirectResponse
    {
        // Authorization
        if ($contest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Can only attach to draft contests
        if ($contest->status !== 'draft') {
            return back()->with('error', 'Can only add problems to draft contests.');
        }

        // Check if already attached
        if ($contest->problems()->where('problem_id', $problem->id)->exists()) {
            return back()->with('error', 'Problem already added to this contest.');
        }

        // Get the next order index
        $maxOrder = $contest->problems()->max('order_index') ?? -1;

        // Attach with default points and order
        $contest->problems()->attach($problem->id, [
            'points' => $request->input('points', 100),
            'order_index' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Problem added successfully!');
    }

    /**
     * Detach a problem from the contest.
     */
    public function detachProblem(Request $request, CustomContest $contest, Problem $problem): RedirectResponse
    {
        // Authorization
        if ($contest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Can only detach from draft contests
        if ($contest->status !== 'draft') {
            return back()->with('error', 'Can only remove problems from draft contests.');
        }

        $contest->problems()->detach($problem->id);

        // Reorder remaining problems
        $this->reorderProblemsAfterDetach($contest);

        return back()->with('success', 'Problem removed successfully!');
    }

    /**
     * Update problem points.
     */
    public function updatePoints(Request $request, CustomContest $contest, Problem $problem): RedirectResponse
    {
        // Authorization
        if ($contest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'points' => 'required|integer|min:1|max:10000',
        ]);

        $contest->problems()->updateExistingPivot($problem->id, [
            'points' => $request->points,
        ]);

        return back()->with('success', 'Points updated successfully!');
    }

    /**
     * Reorder problems.
     */
    public function reorderProblems(Request $request, CustomContest $contest): RedirectResponse|JsonResponse
    {
        // Authorization
        if ($contest->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'problem_ids' => 'required|array',
            'problem_ids.*' => 'exists:problems,id',
        ]);

        foreach ($request->problem_ids as $index => $problemId) {
            $contest->problems()->updateExistingPivot($problemId, [
                'order_index' => $index,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Problems reordered successfully!');
    }

    /**
     * Start participation in a contest.
     */
    public function startParticipation(Request $request, CustomContest $contest): RedirectResponse
    {
        $user = $request->user();

        // Check if contest is active
        if ($contest->status !== 'active') {
            return back()->with('error', 'Contest is not active.');
        }

        // Check if user has already participated
        $existing = CustomContestParticipation::where('custom_contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already participated in this contest.');
        }

        // Create participation
        CustomContestParticipation::create([
            'custom_contest_id' => $contest->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'score' => 0,
            'problems_solved' => 0,
            'solved_problems' => [],
        ]);

        return redirect()
            ->route('custom-contests.show', $contest)
            ->with('success', 'Contest started! Good luck!');
    }

    /**
     * Finish participation in a contest.
     */
    public function finishParticipation(Request $request, CustomContest $contest): RedirectResponse
    {
        $user = $request->user();

        $participation = CustomContestParticipation::where('custom_contest_id', $contest->id)
            ->where('user_id', $user->id)
            ->whereNull('finished_at')
            ->first();

        if (!$participation) {
            return back()->with('error', 'No active participation found.');
        }

        $participation->update([
            'finished_at' => now(),
        ]);

        return redirect()
            ->route('custom-contests.show', $contest)
            ->with('success', 'Contest finished! Your score: ' . $participation->score);
    }

    /**
     * Show contest leaderboard.
     */
    public function leaderboard(Request $request, CustomContest $contest): View
    {
        $participations = $contest->participations()
            ->with('user')
            ->orderBy('score', 'desc')
            ->orderBy('finished_at', 'asc')
            ->get();

        return view('custom-contests.leaderboard', [
            'contest' => $contest,
            'participations' => $participations,
        ]);
    }

    /**
     * Helper: Reorder problems after detaching one.
     */
    private function reorderProblemsAfterDetach(CustomContest $contest): void
    {
        $problems = $contest->problems()->orderBy('order_index')->get();
        
        foreach ($problems as $index => $problem) {
            $contest->problems()->updateExistingPivot($problem->id, [
                'order_index' => $index,
            ]);
        }
    }
}

