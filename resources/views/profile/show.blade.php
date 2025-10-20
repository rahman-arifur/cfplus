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
                    @if (session('status') === 'cf-account-synced')
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                            <p class="text-sm text-green-800">
                                {{ __('Codeforces account synced successfully! Your profile has been updated.') }}
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
                        <div class="flex items-start gap-6">
                            <!-- Avatar -->
                            @if($user->cfAccount->avatar)
                                <div class="flex-shrink-0">
                                    <img src="{{ $user->cfAccount->avatar }}" 
                                         alt="{{ $user->cfAccount->handle }}" 
                                         class="w-24 h-24 rounded-lg border-2 border-gray-200">
                                </div>
                            @endif

                            <!-- Profile Info -->
                            <div class="flex-1 space-y-3">
                                <!-- Handle -->
                                <div>
                                    <p class="text-sm text-gray-600">{{ __('Handle') }}</p>
                                    <a href="https://codeforces.com/profile/{{ $user->cfAccount->handle }}" 
                                       target="_blank" 
                                       class="text-lg font-semibold text-blue-600 hover:underline">
                                        {{ $user->cfAccount->handle }}
                                    </a>
                                </div>

                                <!-- Rating -->
                                @if($user->cfAccount->current_rating)
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">{{ __('Current Rating') }}</p>
                                        <x-cf-rating-badge :rating="$user->cfAccount->current_rating" />
                                    </div>
                                @endif

                                <!-- Max Rating -->
                                @if($user->cfAccount->max_rating)
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">{{ __('Max Rating') }}</p>
                                        <x-cf-rating-badge :rating="$user->cfAccount->max_rating" />
                                    </div>
                                @endif

                                <!-- Location -->
                                @if($user->cfAccount->country || $user->cfAccount->city)
                                    <div>
                                        <p class="text-sm text-gray-600">{{ __('Location') }}</p>
                                        <p class="text-sm">
                                            @if($user->cfAccount->city)
                                                {{ $user->cfAccount->city }}@if($user->cfAccount->country),@endif
                                            @endif
                                            @if($user->cfAccount->country)
                                                {{ $user->cfAccount->country }}
                                            @endif
                                        </p>
                                    </div>
                                @endif

                                <!-- Organization -->
                                @if($user->cfAccount->organization)
                                    <div>
                                        <p class="text-sm text-gray-600">{{ __('Organization') }}</p>
                                        <p class="text-sm">{{ $user->cfAccount->organization }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Last Sync -->
                        @if($user->cfAccount->last_synced_at)
                            <p class="text-xs text-gray-500 mt-6">
                                {{ __('Last synced:') }} {{ $user->cfAccount->last_synced_at->diffForHumans() }}
                            </p>
                        @else
                            <p class="text-xs text-gray-500 mt-6">
                                {{ __('Not synced yet. Click "Sync Now" to fetch data from Codeforces.') }}
                            </p>
                        @endif
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
