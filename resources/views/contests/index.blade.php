<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Contests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Filter Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <a href="{{ route('contests.index', ['filter' => 'upcoming']) }}"
                           class="@if($filter === 'upcoming') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Upcoming
                        </a>
                        <a href="{{ route('contests.index', ['filter' => 'running']) }}"
                           class="@if($filter === 'running') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Running
                        </a>
                        <a href="{{ route('contests.index', ['filter' => 'past']) }}"
                           class="@if($filter === 'past') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Past
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Contests List -->
            <div class="space-y-4">
                @forelse($contests as $contest)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $contest->name }}
                                        </h3>
                                        
                                        <!-- Phase Badge -->
                                        @if($contest->isRunning())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="animate-pulse mr-1.5">●</span> Live
                                            </span>
                                        @elseif($contest->isUpcoming())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Upcoming
                                            </span>
                                        @endif
                                        
                                        <!-- Participated Badge -->
                                        @if(isset($participatedContestsData[$contest->contest_id]))
                                            @php
                                                $contestData = $participatedContestsData[$contest->contest_id];
                                                $ratingChange = $contestData['rating_change'];
                                                $solveCount = $contestData['solve_count'];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                ✓ Participated
                                            </span>
                                            @if($ratingChange > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    +{{ $ratingChange }}
                                                </span>
                                            @elseif($ratingChange < 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ $ratingChange }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $ratingChange }}
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $solveCount }} solved
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                        <!-- Start Time -->
                                        @if($contest->start_time)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $contest->start_time->format('M d, Y H:i') }}
                                                @if($contest->isUpcoming() && $contest->timeUntilStart)
                                                    <span class="ml-1 text-blue-600">({{ $contest->timeUntilStart }})</span>
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <!-- Duration -->
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $contest->formatted_duration }}
                                        </div>
                                        
                                        <!-- Type -->
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            {{ $contest->type }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="ml-4">
                                    <a href="https://codeforces.com/contest/{{ $contest->contest_id }}" 
                                       target="_blank"
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        View Contest →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p>No {{ $filter }} contests found.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination for Past Contests -->
            @if($filter === 'past' && $contests instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-6">
                    {{ $contests->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
