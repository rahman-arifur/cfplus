<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $contest->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Contest Header -->
            <div class="mb-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="flex items-center space-x-3 mb-2">
                            <span class="px-3 py-1 text-sm font-medium rounded-full
                                @if($contest->status === 'completed') bg-green-100 text-green-700
                                @elseif($contest->status === 'active') bg-blue-100 text-blue-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($contest->status) }}
                            </span>
                            @if($contest->isActive())
                                <span class="px-3 py-1 text-sm font-medium bg-red-100 text-red-700 rounded-full animate-pulse">
                                    ⏱️ {{ $contest->remaining_time_formatted }} remaining
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-3">
                        @if($contest->status === 'draft')
                            <form action="{{ route('user-contests.start', $contest) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        style="background-color: #16a34a !important; color: white !important;"
                                        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                                    🚀 Start Contest
                                </button>
                            </form>
                        @elseif($contest->status === 'active')
                            <a href="{{ route('user-contests.participate', $contest) }}" 
                               style="background-color: #2563eb !important; color: white !important;"
                               class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                                ▶️ Continue Contest
                            </a>
                        @endif
                        
                        <a href="{{ route('user-contests.index') }}" 
                           style="background-color: #374151 !important; color: white !important;"
                           class="inline-block bg-gray-700 hover:bg-gray-800 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg border border-gray-600">
                            ← Back to Contests
                        </a>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Contest Information -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Contest Details -->
                <div class="lg:col-span-2 bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Contest Details</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-gray-500 text-sm">Rating Range</div>
                            <div class="text-lg font-medium">{{ $contest->rating_min }} - {{ $contest->rating_max }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm">Problem Count</div>
                            <div class="text-lg font-medium">{{ $contest->problem_count }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm">Duration</div>
                            <div class="text-lg font-medium">{{ floor($contest->duration_minutes / 60) }}h {{ $contest->duration_minutes % 60 }}m</div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm">Progress</div>
                            <div class="text-lg font-medium">{{ $contest->progress }}%</div>
                        </div>
                        @if($contest->actual_rating)
                            <div>
                                <div class="text-gray-500 text-sm">Rating</div>
                                <div class="text-lg font-medium text-blue-600">{{ $contest->actual_rating }}</div>
                            </div>
                        @endif
                        @if($contest->rating_change)
                            <div>
                                <div class="text-gray-500 text-sm">Rating Change</div>
                                <div class="text-lg font-medium {{ $contest->rating_change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $contest->rating_change > 0 ? '+' : '' }}{{ $contest->rating_change }}
                                </div>
                            </div>
                        @endif
                        @if($contest->performance_rating)
                            <div>
                                <div class="text-gray-500 text-sm">Performance</div>
                                <div class="text-lg font-medium text-purple-600">{{ $contest->performance_rating }}</div>
                            </div>
                        @endif
                        @if($contest->started_at)
                            <div>
                                <div class="text-gray-500 text-sm">Started</div>
                                <div class="text-lg font-medium">{{ \Carbon\Carbon::parse($contest->started_at)->format('M j, Y H:i') }}</div>
                            </div>
                        @endif
                        @if($contest->completed_at)
                            <div>
                                <div class="text-gray-500 text-sm">Completed</div>
                                <div class="text-lg font-medium">{{ \Carbon\Carbon::parse($contest->completed_at)->format('M j, Y H:i') }}</div>
                            </div>
                        @endif
                    </div>
                    
                    @if($contest->tags && is_array($contest->tags) && count($contest->tags) > 0)
                        <div class="mt-4">
                            <div class="text-gray-500 text-sm mb-2">Tags</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($contest->tags as $tag)
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 text-sm rounded">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Statistics -->
                <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Statistics</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Problems Solved</span>
                            <span class="font-semibold text-green-600">{{ $contest->problems->where('pivot.solved_during_contest', true)->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Problems</span>
                            <span class="font-semibold">{{ $contest->problems->count() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Accuracy</span>
                            <span class="font-semibold">
                                @if($contest->problems->count() > 0)
                                    {{ round(($contest->problems->where('pivot.solved_during_contest', true)->count() / $contest->problems->count()) * 100) }}%
                                @else
                                    0%
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Avg Rating</span>
                            <span class="font-semibold">
                                @if($contest->problems->count() > 0)
                                    {{ round($contest->problems->avg('rating')) }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        @if($contest->status === 'completed')
                            <div class="flex justify-between">
                                <span class="text-gray-500">Duration Used</span>
                                <span class="font-semibold">
                                    {{ $contest->started_at->diffInMinutes($contest->completed_at) }}m
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rating Distribution Chart -->
            @if($contest->problems->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">📊 Rating Distribution</h2>
                <div class="h-64">
                    <canvas id="ratingChart"></canvas>
                </div>
            </div>
            @endif

            <!-- Problems List -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Problems</h2>
                
                @if($contest->problems->isEmpty())
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-2">No problems generated yet</div>
                        @if($contest->status === 'draft')
                            <p class="text-sm text-gray-500">Problems will be generated when you start the contest</p>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">#</th>
                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Problem</th>
                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Rating</th>
                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Tags</th>
                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Status</th>
                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contest->problems as $index => $problem)
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-4 px-2 font-bold text-gray-500">{{ chr(65 + $index) }}</td>
                                        <td class="py-4 px-2">
                                            <div class="font-semibold text-gray-800">{{ $problem->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $problem->contest_id }}{{ $problem->index }}</div>
                                        </td>
                                        <td class="py-4 px-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded
                                                @if($problem->rating <= 1000) bg-gray-100 text-gray-600
                                                @elseif($problem->rating <= 1200) bg-green-100 text-green-700
                                                @elseif($problem->rating <= 1400) bg-cyan-100 text-cyan-700
                                                @elseif($problem->rating <= 1600) bg-blue-100 text-blue-700
                                                @elseif($problem->rating <= 1900) bg-purple-100 text-purple-700
                                                @elseif($problem->rating <= 2100) bg-yellow-100 text-yellow-700
                                                @elseif($problem->rating <= 2400) bg-orange-100 text-orange-700
                                                @else bg-red-100 text-red-700 @endif">
                                                {{ $problem->rating ?? 'Unrated' }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-2">
                                            @if($problem->tags && is_array($problem->tags) && count($problem->tags) > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach(array_slice($problem->tags, 0, 3) as $tag)
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">{{ $tag }}</span>
                                                    @endforeach
                                                    @if(count($problem->tags) > 3)
                                                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">+{{ count($problem->tags) - 3 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-4 px-2">
                                            @if($problem->pivot->solved_during_contest)
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded">
                                                    ✓ Solved
                                                </span>
                                                @if($problem->pivot->solved_at)
                                                    <div class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($problem->pivot->solved_at)->format('H:i:s') }}</div>
                                                @endif
                                            @elseif($problem->pivot->solved_at)
                                                <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded">
                                                    ✓ Previously Solved
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded">
                                                    Unsolved
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-2">
                                            <a href="{{ $problem->url }}" 
                                               target="_blank"
                                               class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-1 px-3 rounded transition duration-200">
                                                Open
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($contest->problems->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('ratingChart');
            if (ctx) {
                // Group problems by rating range
                const problems = @json($contest->problems->pluck('rating'));
                const ratingRanges = {
                    '800-1000': 0,
                    '1000-1200': 0,
                    '1200-1400': 0,
                    '1400-1600': 0,
                    '1600-1900': 0,
                    '1900-2100': 0,
                    '2100-2400': 0,
                    '2400+': 0
                };

                problems.forEach(rating => {
                    if (rating < 1000) ratingRanges['800-1000']++;
                    else if (rating < 1200) ratingRanges['1000-1200']++;
                    else if (rating < 1400) ratingRanges['1200-1400']++;
                    else if (rating < 1600) ratingRanges['1400-1600']++;
                    else if (rating < 1900) ratingRanges['1600-1900']++;
                    else if (rating < 2100) ratingRanges['1900-2100']++;
                    else if (rating < 2400) ratingRanges['2100-2400']++;
                    else ratingRanges['2400+']++;
                });

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(ratingRanges),
                        datasets: [{
                            label: 'Number of Problems',
                            data: Object.values(ratingRanges),
                            backgroundColor: [
                                'rgba(156, 163, 175, 0.7)',  // gray
                                'rgba(34, 197, 94, 0.7)',    // green
                                'rgba(6, 182, 212, 0.7)',    // cyan
                                'rgba(59, 130, 246, 0.7)',   // blue
                                'rgba(168, 85, 247, 0.7)',   // purple
                                'rgba(234, 179, 8, 0.7)',    // yellow
                                'rgba(249, 115, 22, 0.7)',   // orange
                                'rgba(239, 68, 68, 0.7)'     // red
                            ],
                            borderColor: [
                                'rgb(107, 114, 128)',
                                'rgb(22, 163, 74)',
                                'rgb(8, 145, 178)',
                                'rgb(37, 99, 235)',
                                'rgb(147, 51, 234)',
                                'rgb(202, 138, 4)',
                                'rgb(234, 88, 12)',
                                'rgb(220, 38, 38)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Problem Difficulty Distribution',
                                font: {
                                    size: 14,
                                    weight: 'normal'
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                },
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Rating Range'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif
</x-app-layout>