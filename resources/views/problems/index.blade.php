<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Problems') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Sync Form (outside filter form) -->
            @if($userStats)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $userStats['solved_count'] }}</div>
                                <div class="text-xs text-gray-600">Solved</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-yellow-600">{{ $userStats['attempted_count'] }}</div>
                                <div class="text-xs text-gray-600">Attempted</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-600">{{ $userStats['total_count'] - $userStats['solved_count'] - $userStats['attempted_count'] }}</div>
                                <div class="text-xs text-gray-600">Unsolved</div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col gap-2">
                            <!-- Sync Button -->
                            <form method="POST" action="{{ route('profile.sync-cf') }}" class="inline" id="sync-form">
                                @csrf
                                <input type="hidden" name="sync_now" value="1">
                                <button type="submit" id="sync-btn"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg id="sync-icon" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span id="sync-text">Sync Now</span>
                                </button>
                            </form>
                            
                            <!-- Status Filter Buttons -->
                            <div class="flex gap-2">
                                <button type="button" onclick="setStatusFilter('')" 
                                    class="px-3 py-1.5 text-xs rounded {{ empty($filters['status_filter']) ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    All
                                </button>
                                <button type="button" onclick="setStatusFilter('unsolved')" 
                                    class="px-3 py-1.5 text-xs rounded {{ ($filters['status_filter'] ?? '') == 'unsolved' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    Unsolved Only
                                </button>
                                <button type="button" onclick="setStatusFilter('attempted')" 
                                    class="px-3 py-1.5 text-xs rounded {{ ($filters['status_filter'] ?? '') == 'attempted' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    Attempted
                                </button>
                                <button type="button" onclick="setStatusFilter('solved')" 
                                    class="px-3 py-1.5 text-xs rounded {{ ($filters['status_filter'] ?? '') == 'solved' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    Solved
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Filter Problems</h3>
                    <form id="filter-form" method="GET" action="{{ route('problems.index') }}" class="space-y-4">
                        <!-- Success/Error Messages -->
                        @if(session('message'))
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4" id="success-message">
                                <div class="flex items-center gap-2">
                                    <span class="text-green-600">✓</span>
                                    <p class="text-sm text-green-800">{{ session('message') }}</p>
                                </div>
                            </div>
                            @if(session('status') === 'cf-synced')
                                <script>
                                    // Auto-refresh after 1 second to show updated stats
                                    setTimeout(function() {
                                        window.location.href = window.location.pathname + window.location.search;
                                    }, 1000);
                                </script>
                            @endif
                        @endif

                        @if(session('error'))
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-red-600">✗</span>
                                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Hidden input for status filter -->
                        <input type="hidden" name="status_filter" id="status_filter" value="{{ $filters['status_filter'] ?? '' }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input type="text" name="search" id="search" value="{{ $filters['search'] ?? '' }}" 
                                    placeholder="Problem name or code" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Min Rating -->
                            <div>
                                <label for="min_rating" class="block text-sm font-medium text-gray-700 mb-1">Min Rating</label>
                                <select name="min_rating" id="min_rating" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any</option>
                                    @for ($rating = 800; $rating <= 3500; $rating += 100)
                                        <option value="{{ $rating }}" {{ ($filters['min_rating'] ?? '') == $rating ? 'selected' : '' }}>
                                            {{ $rating }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Max Rating -->
                            <div>
                                <label for="max_rating" class="block text-sm font-medium text-gray-700 mb-1">Max Rating</label>
                                <select name="max_rating" id="max_rating" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any</option>
                                    @for ($rating = 800; $rating <= 3500; $rating += 100)
                                        <option value="{{ $rating }}" {{ ($filters['max_rating'] ?? '') == $rating ? 'selected' : '' }}>
                                            {{ $rating }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Sort By -->
                            <div>
                                <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                                <select name="sort_by" id="sort_by" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="rating" {{ ($filters['sort_by'] ?? 'rating') == 'rating' ? 'selected' : '' }}>Rating</option>
                                    <option value="solved_count" {{ ($filters['sort_by'] ?? '') == 'solved_count' ? 'selected' : '' }}>Popularity</option>
                                    <option value="code" {{ ($filters['sort_by'] ?? '') == 'code' ? 'selected' : '' }}>Code</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                            <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto p-2 border rounded-md bg-gray-50">
                                @foreach($allTags as $tag)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="tags[]" value="{{ $tag }}" 
                                            {{ in_array($tag, $filters['tags'] ?? []) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $tag }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                                🔍 Apply Filters
                            </button>
                            <a href="{{ route('problems.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Clear Filters
                            </a>
                            <button type="button" onclick="randomProblem()" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                🎲 Random Problem
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">
                            {{ $problems->total() }} Problems Found
                        </h3>
                    </div>

                    @if($problems->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-gray-500 text-lg">No problems found matching your filters.</p>
                            <p class="text-gray-400 text-sm mt-2">Try adjusting your search criteria.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @if($userStats)
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        @endif
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Problem Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tags</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solved</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($problems as $problem)
                                        <tr class="hover:bg-gray-50 {{ isset($problem->user_status) && $problem->user_status === 'solved' ? 'bg-green-50' : '' }}">
                                            @if($userStats)
                                                <td class="px-3 py-4 whitespace-nowrap text-center">
                                                    @if(isset($problem->user_status))
                                                        @if($problem->user_status === 'solved')
                                                            <span class="text-green-600 text-xl font-bold" title="Solved">✓</span>
                                                        @elseif($problem->user_status === 'attempted')
                                                            <span class="text-yellow-600 text-xl" title="Attempted">○</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-300 text-xl">○</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $problem->code }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <a href="{{ $problem->url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                                    {{ $problem->name }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($problem->rating)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $problem->rating_color }}-100 text-{{ $problem->rating_color }}-800">
                                                        {{ $problem->rating }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                <div class="flex flex-wrap gap-1">
                                                    @if($problem->tags && is_array($problem->tags))
                                                        @foreach(array_slice($problem->tags, 0, 2) as $tag)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                                {{ $tag }}
                                                            </span>
                                                        @endforeach
                                                        @if(count($problem->tags) > 2)
                                                            <span class="text-xs text-gray-500">+{{ count($problem->tags) - 2 }}</span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ number_format($problem->solved_count) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ $problem->url }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                                    Solve →
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $problems->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function randomProblem() {
            const filterForm = document.getElementById('filter-form');
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            window.location.href = "{{ route('problems.random') }}?" + params;
        }

        function setStatusFilter(status) {
            document.getElementById('status_filter').value = status;
            document.getElementById('filter-form').submit();
        }
    </script>
</x-app-layout>
