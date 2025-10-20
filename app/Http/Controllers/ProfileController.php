<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkCfAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Jobs\SyncCfAccount;
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
        
        $cfAccount = $user->cfAccount()->updateOrCreate(
            ['user_id' => $user->id],
            ['handle' => $request->validated('handle')]
        );

        // Dispatch sync job to fetch data from Codeforces API
        SyncCfAccount::dispatch($cfAccount);

        return Redirect::route('profile.show')->with('status', 'cf-account-linked');
    }

    /**
     * Manually sync Codeforces account data.
     */
    public function syncCf(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->cfAccount) {
            return Redirect::route('profile.show')
                ->with('error', 'No Codeforces account linked. Please link your account first.');
        }

        // Dispatch sync job
        SyncCfAccount::dispatch($user->cfAccount);

        return Redirect::route('profile.show')
            ->with('status', 'cf-sync-queued');
    }
}
