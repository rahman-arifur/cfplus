<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Codeforces Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Link your Codeforces handle to sync your profile, ratings, and submissions.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.link-cf') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="handle" :value="__('Codeforces Handle')" />
            <x-text-input 
                id="handle" 
                name="handle" 
                type="text" 
                class="mt-1 block w-full" 
                :value="old('handle', $user->cfAccount?->handle)" 
                placeholder="e.g., tourist"
                autofocus 
            />
            <x-input-error class="mt-2" :messages="$errors->get('handle')" />
            
            @if($user->cfAccount)
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Current handle: ') }}
                    <a href="https://codeforces.com/profile/{{ $user->cfAccount->handle }}" 
                       target="_blank" 
                       class="text-blue-600 hover:underline">
                        {{ $user->cfAccount->handle }}
                    </a>
                    @if($user->cfAccount->last_synced_at)
                        <span class="text-gray-500">
                            ({{ __('Last synced: ') }}{{ $user->cfAccount->last_synced_at->diffForHumans() }})
                        </span>
                    @endif
                </p>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'cf-account-linked')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
