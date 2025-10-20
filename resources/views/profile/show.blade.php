<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- User Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $user->email }}</p>
                    <p class="text-sm text-gray-600 mt-2">
                        <span class="font-medium">{{ __('Timezone:') }}</span> {{ $user->timezone }}
                    </p>
                    
                    <div class="mt-6">
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit Profile') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Codeforces Account -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">{{ __('Codeforces Account') }}</h3>
                        
                        @if($user->cfAccount)
                            <form method="POST" action="{{ route('profile.sync-cf') }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    {{ __('Sync Now') }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Status Messages -->
                    @if (session('status') === 'cf-account-linked')
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                            <p class="text-sm text-green-800">
                                {{ __('Codeforces account linked successfully! Syncing data in background...') }}
                            </p>
                        </div>
                    @endif

                    @if (session('status') === 'cf-sync-queued')
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                            <p class="text-sm text-blue-800">
                                {{ __('Sync queued! Data will be updated shortly. Refresh the page in a few moments.') }}
                            </p>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                            <p class="text-sm text-red-800">{{ session('error') }}</p>
                        </div>
                    @endif
                    
                    @if($user->cfAccount)
                        <div class="space-y-3">
                            <p class="text-sm">
                                <span class="font-medium text-gray-700">{{ __('Handle:') }}</span>
                                <a href="https://codeforces.com/profile/{{ $user->cfAccount->handle }}" 
                                   target="_blank" 
                                   class="text-blue-600 hover:underline ml-2">
                                    {{ $user->cfAccount->handle }}
                                </a>
                            </p>
                            
                            @if($user->cfAccount->current_rating)
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700">{{ __('Current Rating:') }}</span>
                                    <span class="ml-2">{{ $user->cfAccount->current_rating }}</span>
                                </p>
                            @endif
                            
                            @if($user->cfAccount->max_rating)
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700">{{ __('Max Rating:') }}</span>
                                    <span class="ml-2">{{ $user->cfAccount->max_rating }}</span>
                                </p>
                            @endif
                            
                            @if($user->cfAccount->country)
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700">{{ __('Country:') }}</span>
                                    <span class="ml-2">{{ $user->cfAccount->country }}</span>
                                </p>
                            @endif

                            @if($user->cfAccount->city)
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700">{{ __('City:') }}</span>
                                    <span class="ml-2">{{ $user->cfAccount->city }}</span>
                                </p>
                            @endif

                            @if($user->cfAccount->organization)
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700">{{ __('Organization:') }}</span>
                                    <span class="ml-2">{{ $user->cfAccount->organization }}</span>
                                </p>
                            @endif
                            
                            @if($user->cfAccount->last_synced_at)
                                <p class="text-xs text-gray-500 mt-4">
                                    {{ __('Last synced:') }} {{ $user->cfAccount->last_synced_at->diffForHumans() }}
                                </p>
                            @else
                                <p class="text-xs text-gray-500 mt-4">
                                    {{ __('Not synced yet. Click "Sync Now" to fetch data from Codeforces.') }}
                                </p>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-gray-600 mb-4">
                            {{ __('No Codeforces account linked yet.') }}
                        </p>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Link Codeforces Account') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
