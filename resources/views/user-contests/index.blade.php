<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Custom Contests') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <p class="text-gray-600">Create and manage your practice contests</p>
                </div>
                <a href="{{ route('user-contests.create') }}" 
                   style="background-color: #2563eb !important; color: white !important;"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                    ➕ New Contest
                </a>
            </div>

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <!-- Contest Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-blue-600">{{ $contests->where('status', 'completed')->count() }}</div>
                    <div class="text-gray-500 text-sm">Completed</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-green-600">{{ $contests->where('status', 'active')->count() }}</div>
                    <div class="text-gray-500 text-sm">Active</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-yellow-600">{{ $contests->where('status', 'draft')->count() }}</div>
                    <div class="text-gray-500 text-sm">Drafts</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                    <div class="text-2xl font-bold text-purple-600">{{ $contests->sum(function($contest) { return $contest->problems->where('pivot.solved_during_contest', true)->count(); }) }}</div>
                    <div class="text-gray-500 text-sm">Problems Solved</div>
                </div>
            </div>

            <!-- Performance Chart -->
            @if($completedContests->isNotEmpty())
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">📈 Contest Performance History</h2>
                <div class="h-80">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
            @endif

            <!-- Contest List -->
            @if ($contests->isEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center py-12">
                        <div class="text-6xl text-gray-300 mb-4">📝</div>
                        <h3 class="text-xl font-semibold mb-2 text-gray-800">No contests yet</h3>
                        <p class="text-gray-600 mb-6">Create your first custom contest to start practicing!</p>
                        <a href="{{ route('user-contests.create') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                            Create Your First Contest
                        </a>
                    </div>
                </div>
            @else
                <div class="space-y-4">
                    @foreach ($contests as $contest)
                        <div class="bg-white border border-gray-200 rounded-lg p-6 hover:border-gray-300 transition duration-200 shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <a href="{{ route('user-contests.show', $contest) }}" class="text-xl font-semibold text-gray-800 hover:text-blue-600 transition duration-200">
                                            {{ $contest->title }}
                                        </a>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($contest->status === 'completed') bg-green-100 text-green-700
                                            @elseif($contest->status === 'active') bg-blue-100 text-blue-700
                                            @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ ucfirst($contest->status) }}
                                        </span>
                                        @if($contest->isActive())
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full animate-pulse">
                                                {{ $contest->remaining_time }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Rating:</span>
                                            <span class="font-medium">{{ $contest->rating_min }}-{{ $contest->rating_max }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Problems:</span>
                                            <span class="font-medium">{{ $contest->problem_count }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Duration:</span>
                                            <span class="font-medium">{{ floor($contest->duration_minutes / 60) }}h {{ $contest->duration_minutes % 60 }}m</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Progress:</span>
                                            <span class="font-medium">{{ $contest->progress }}%</span>
                                        </div>
                                    </div>
                                    @if($contest->tags && is_array($contest->tags) && count($contest->tags) > 0)
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @foreach($contest->tags as $tag)
                                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Contest Actions -->
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-500">
                                    @if($contest->status === 'draft')
                                        Created {{ $contest->created_at->diffForHumans() }}
                                    @elseif($contest->status === 'active')
                                        Started {{ $contest->started_at->diffForHumans() }}
                                    @else
                                        Completed {{ $contest->completed_at->diffForHumans() }}
                                    @endif
                                </div>
                                
                                <div class="flex flex-wrap gap-2">
                                    @if($contest->status === 'draft')
                                        <form action="{{ route('user-contests.start', $contest) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    style="background-color: #16a34a !important; color: white !important;"
                                                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded transition duration-200 shadow-sm hover:shadow-md">
                                                🚀 Start Contest
                                            </button>
                                        </form>
                                    @elseif($contest->status === 'active')
                                        <a href="{{ route('user-contests.participate', $contest) }}" 
                                           style="background-color: #2563eb !important; color: white !important;"
                                           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition duration-200 shadow-sm hover:shadow-md">
                                            ▶️ Continue Contest
                                        </a>
                                    @endif
                                    
                                    <a href="{{ route('user-contests.show', $contest) }}" 
                                       style="background-color: #4b5563 !important; color: white !important;"
                                       class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded transition duration-200 shadow-sm hover:shadow-md">
                                        👁️ View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($contests->hasPages())
                    <div class="mt-8">
                        {{ $contests->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if($completedContests->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('performanceChart');
            if (ctx) {
                // Get completed contests data from controller
                const completedContests = @json($completedContests);

                // Calculate performance score (0-100 based on progress and difficulty)
                const performanceData = completedContests.map(contest => {
                    // Base score from progress
                    let score = contest.progress;
                    
                    // Bonus for difficulty (higher rating = more bonus)
                    const difficultyBonus = Math.min(20, (contest.avg_rating - 800) / 100);
                    score = Math.min(100, score + difficultyBonus);
                    
                    return Math.round(score);
                });

                const labels = completedContests.map(c => c.title);
                const dates = completedContests.map(c => c.completed_at);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [{
                            label: 'Performance Score',
                            data: performanceData,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Your Contest Performance Over Time',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const contest = completedContests[context.dataIndex];
                                        return [
                                            `Performance: ${context.parsed.y}/100`,
                                            `Progress: ${contest.progress}%`,
                                            `Solved: ${contest.solved}/${contest.total}`,
                                            `Avg Rating: ${Math.round(contest.avg_rating)}`
                                        ];
                                    },
                                    title: function(context) {
                                        return completedContests[context[0].dataIndex].title;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100,
                                title: {
                                    display: true,
                                    text: 'Performance Score'
                                },
                                ticks: {
                                    callback: function(value) {
                                        return value + '/100';
                                    }
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Contest Date'
                                },
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index',
                        }
                    }
                });
            }
        });
    </script>
    @endif
</x-app-layout>