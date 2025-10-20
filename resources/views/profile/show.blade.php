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
                    <h3 class="text-lg font-medium mb-4">{{ __('Codeforces Account') }}</h3>
                    
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
                            
                            @if($user->cfAccount->last_synced_at)
                                <p class="text-xs text-gray-500 mt-4">
                                    {{ __('Last synced:') }} {{ $user->cfAccount->last_synced_at->diffForHumans() }}
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
