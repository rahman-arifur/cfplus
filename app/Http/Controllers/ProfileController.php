<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkCfAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\SyncCfAccount;
use App\Services\CodeforcesApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the user's profile.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Link or update the user's Codeforces account.
     */
    public function linkCf(LinkCfAccountRequest $request): RedirectResponse
    {
        $user = $request->user();
        $newHandle = $request->validated('handle');
        
        $cfAccount = $user->cfAccount;
        $isUpdatingHandle = $cfAccount && $cfAccount->handle !== $newHandle;
        
        // If updating to a different handle, clear old data
        if ($isUpdatingHandle) {
            $oldHandle = $cfAccount->handle;
            
            // Clear old rating snapshots
            $cfAccount->ratingSnapshots()->delete();
            
            // Clear cached API data for old handle
            $cfApi = app(CodeforcesApiService::class);
            $cfApi->clearCacheForHandle($oldHandle);
        }
        
        // Update or create CF account
        $cfAccount = $user->cfAccount()->updateOrCreate(
            ['user_id' => $user->id],
            ['handle' => $newHandle]
        );

        // Dispatch sync job synchronously to fetch data immediately
        SyncCfAccount::dispatchSync($cfAccount);

        return Redirect::route('profile.show')->with('status', 'cf-account-synced');
    }

    /**
     * Manually sync Codeforces account data.
     */
    public function syncCf(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->cfAccount) {
            return back()
                ->with('error', 'No Codeforces account linked. Please link your account first.');
        }

        // Check if we should sync immediately (for better UX on problems page)
        $syncNow = $request->get('sync_now', false);
        
        if ($syncNow) {
            try {
                // Dispatch synchronously for immediate feedback
                SyncCfAccount::dispatchSync($user->cfAccount);
                
                // Reload the user's relationships to get fresh data
                $user->load('cfAccount');
                
                // Count updated problems
                $solvedCount = $user->solvedProblems()->count();
                $attemptedCount = $user->attemptedProblems()->count();
                
                return back()
                    ->with('status', 'cf-synced')
                    ->with('message', "✓ Sync completed! You have {$solvedCount} solved and {$attemptedCount} attempted problems.");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Sync failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return back()
                    ->with('error', 'Sync failed: ' . $e->getMessage());
            }
        }

        // Dispatch async job
        SyncCfAccount::dispatch($user->cfAccount);

        // Redirect back to where they came from, or default to profile
        return back()
            ->with('status', 'cf-sync-queued')
            ->with('message', 'Sync started! Your problem status will be updated shortly.');
    }
}
