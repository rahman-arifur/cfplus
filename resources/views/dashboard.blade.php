<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- 1. Hero Section - User Rating Card -->
            <div class="bg-gradient-to-r from-blue-600 via-blue-500 to-purple-600 rounded-xl shadow-xl p-8 text-white">
                <div class="flex items-center justify-between flex-wrap gap-6">
                    <div class="flex items-center gap-6">
                        <!-- Avatar -->
                        @if($cfAccount && $cfAccount->avatar)
                            <img src="{{ $cfAccount->avatar }}" 
                                 alt="{{ $cfAccount->handle }}" 
                                 class="w-24 h-24 rounded-full border-4 border-white shadow-lg">
                        @else
                            <div class="w-24 h-24 rounded-full border-4 border-white bg-white/20 flex items-center justify-center text-4xl">
                                👤
                            </div>
                        @endif

                        <!-- User Info -->
                        <div>
                            <h1 class="text-3xl font-bold mb-2">Welcome back, {{ $user->name }}! 👋</h1>
                            @if($cfAccount)
                                <p class="text-white/90 text-lg">
                                    <a href="https://codeforces.com/profile/{{ $cfAccount->handle }}" 
                                       target="_blank" 
                                       class="hover:underline font-medium">
                                        {{ $cfAccount->handle }}
                                    </a>
                                </p>
                            @else
                                <p class="text-white/90">
                                    <a href="{{ route('profile.edit') }}" class="hover:underline">
                                        Link your Codeforces account →
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Rating Info -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 min-w-[280px]">
                        <div class="space-y-3">
                            <div>
                                <div class="text-white/70 text-sm">Current Rating</div>
                                <div class="text-4xl font-bold">{{ $currentRating }} ★</div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <div class="text-white/70">Peak</div>
                                    <div class="text-xl font-semibold">{{ $peakRating }} ⬆️</div>
                                </div>
                                <div>
                                    <div class="text-white/70">Change</div>
                                    <div class="text-xl font-semibold {{ $latestRatingChange >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                        {{ $latestRatingChange >= 0 ? '+' : '' }}{{ $latestRatingChange }} 📈
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-white/20">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $rank['bg'] }} {{ $rank['color'] }}">
                                    {{ $rank['emoji'] }} {{ $rank['name'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Quick Stats Grid (4 Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Contests Completed -->
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-6 border-l-4 border-blue-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-sm font-medium mb-1">Contests</div>
                            <div class="text-3xl font-bold text-blue-600">{{ $totalContests }}</div>
                            <div class="text-gray-400 text-xs mt-1">Completed</div>
                        </div>
                        <div class="text-4xl">🏆</div>
                    </div>
                </div>

                <!-- Problems Solved -->
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-6 border-l-4 border-green-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-sm font-medium mb-1">Problems</div>
                            <div class="text-3xl font-bold text-green-600">{{ $totalProblemsSolved }}</div>
                            <div class="text-gray-400 text-xs mt-1">Solved</div>
                        </div>
                        <div class="text-4xl">✅</div>
                    </div>
                </div>

                <!-- Accuracy -->
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-6 border-l-4 border-purple-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-sm font-medium mb-1">Accuracy</div>
                            <div class="text-3xl font-bold text-purple-600">{{ $accuracy }}%</div>
                            <div class="text-gray-400 text-xs mt-1">Success Rate</div>
                        </div>
                        <div class="text-4xl">🎯</div>
                    </div>
                </div>

                <!-- Streak -->
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 p-6 border-l-4 border-orange-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-500 text-sm font-medium mb-1">Streak</div>
                            <div class="text-3xl font-bold text-orange-600">{{ $streak }}</div>
                            <div class="text-gray-400 text-xs mt-1">{{ $streak === 1 ? 'Day' : 'Days' }} Active</div>
                        </div>
                        <div class="text-4xl">🔥</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- 3. Rating Progress Chart -->
                    @if($ratingHistory->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">📈 Rating Progress</h3>
                        <div class="h-80">
                            <canvas id="ratingChart"></canvas>
                        </div>
                    </div>
                    @endif

                    <!-- 5. Quick Actions -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">⚡ Quick Actions</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="{{ route('user-contests.create') }}" 
                               class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-xl hover:from-blue-100 hover:to-blue-200 hover:border-blue-400 hover:shadow-md transition-all duration-200 group">
                                <div class="text-3xl">➕</div>
                                <div>
                                    <div class="font-semibold text-gray-800 group-hover:text-blue-700">Create Contest</div>
                                    <div class="text-sm text-gray-600">Start a new practice session</div>
                                </div>
                            </a>

                            <a href="{{ route('problems.random') }}" 
                               class="flex items-center gap-3 p-4 bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-300 rounded-xl hover:from-green-100 hover:to-green-200 hover:border-green-400 hover:shadow-md transition-all duration-200 group">
                                <div class="text-3xl">🔍</div>
                                <div>
                                    <div class="font-semibold text-gray-800 group-hover:text-green-700">Random Problem</div>
                                    <div class="text-sm text-gray-600">Get a problem to solve</div>
                                </div>
                            </a>

                            <a href="{{ route('stats.index') }}" 
                               class="flex items-center gap-3 p-4 bg-gradient-to-r from-purple-50 to-purple-100 border-2 border-purple-300 rounded-xl hover:from-purple-100 hover:to-purple-200 hover:border-purple-400 hover:shadow-md transition-all duration-200 group">
                                <div class="text-3xl">📊</div>
                                <div>
                                    <div class="font-semibold text-gray-800 group-hover:text-purple-700">View Statistics</div>
                                    <div class="text-sm text-gray-600">Detailed analytics</div>
                                </div>
                            </a>

                            @if($cfAccount)
                            <form method="POST" action="{{ route('profile.sync-cf') }}">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-3 p-4 bg-gradient-to-r from-orange-50 to-orange-100 border-2 border-orange-300 rounded-xl hover:from-orange-100 hover:to-orange-200 hover:border-orange-400 hover:shadow-md transition-all duration-200 group">
                                    <div class="text-3xl">🔄</div>
                                    <div class="text-left">
                                        <div class="font-semibold text-gray-800 group-hover:text-orange-700">Sync CF Data</div>
                                        <div class="text-sm text-gray-600">Update from Codeforces</div>
                                    </div>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                </div>

                <!-- Right Column (1/3 width) -->
                <div class="space-y-6">
                    
                    <!-- 4. Recent Activity Feed -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">📅 Recent Activity</h3>
                        
                        @if(empty($recentActivity))
                            <div class="text-center py-8 text-gray-400">
                                <div class="text-4xl mb-2">📝</div>
                                <p class="text-sm">No activity yet</p>
                                <p class="text-xs mt-1">Complete a contest to see your activity</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($recentActivity as $activity)
                                    <div class="border-l-4 border-{{ str_contains($activity['color'], 'blue') ? 'blue' : (str_contains($activity['color'], 'yellow') ? 'yellow' : 'gray') }}-400 pl-4 py-2">
                                        <div class="flex items-start gap-2">
                                            <span class="text-xl">{{ $activity['icon'] }}</span>
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-800 text-sm">{{ $activity['title'] }}</div>
                                                <div class="text-xs text-gray-500 mt-1">{{ $activity['details'] }}</div>
                                                <div class="text-xs text-gray-400 mt-1">{{ $activity['time']->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- 7. Upcoming & Active Contests -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-xl font-semibold mb-4 text-gray-800">🎯 Active & Upcoming</h3>
                        
                        @if($activeContestsList->isEmpty() && $draftContestsList->isEmpty())
                            <div class="text-center py-8 text-gray-400">
                                <div class="text-4xl mb-2">📋</div>
                                <p class="text-sm">No active contests</p>
                                <a href="{{ route('user-contests.create') }}" class="text-blue-600 hover:underline text-xs mt-2 inline-block">
                                    Create your first contest →
                                </a>
                            </div>
                        @else
                            <div class="space-y-3">
                                <!-- Active Contests -->
                                @foreach($activeContestsList as $contest)
                                    <div class="border border-blue-200 bg-blue-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-semibold text-blue-600 uppercase">Active</span>
                                            @if($contest->remaining_time)
                                                <span class="text-xs bg-blue-600 text-white px-2 py-1 rounded animate-pulse">
                                                    {{ $contest->remaining_time_formatted }}
                                                </span>
                                            @endif
                                        </div>
                                        <a href="{{ route('user-contests.participate', $contest) }}" 
                                           class="font-semibold text-gray-800 hover:text-blue-600 block mb-1">
                                            {{ $contest->title }}
                                        </a>
                                        <div class="text-xs text-gray-600">
                                            Progress: {{ $contest->progress }}%
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Draft Contests -->
                                @foreach($draftContestsList as $contest)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <span class="text-xs font-semibold text-gray-500 uppercase mb-2 block">Draft</span>
                                        <a href="{{ route('user-contests.show', $contest) }}" 
                                           class="font-semibold text-gray-800 hover:text-gray-600 block mb-1">
                                            {{ $contest->title }}
                                        </a>
                                        <div class="text-xs text-gray-500">
                                            {{ $contest->problem_count }} problems • {{ $contest->duration_minutes }}min
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

    @if($ratingHistory->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('ratingChart');
            if (!ctx) return;

            const ratingData = @json($ratingHistory);
            
            const labels = ratingData.map(item => item.date);
            const actualRatings = ratingData.map(item => item.actual_rating);
            const performanceRatings = ratingData.map(item => item.performance_rating);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Rating',
                            data: actualRatings,
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
                        },
                        {
                            label: 'Performance',
                            data: performanceRatings,
                            borderColor: 'rgb(168, 85, 247)',
                            backgroundColor: 'rgba(168, 85, 247, 0.1)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(168, 85, 247)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const index = context.dataIndex;
                                    const contest = ratingData[index];
                                    
                                    if (context.dataset.label === 'Rating') {
                                        return [
                                            `Rating: ${contest.actual_rating}`,
                                            `Change: ${contest.rating_change >= 0 ? '+' : ''}${contest.rating_change}`,
                                            `Performance: ${contest.performance_rating}`
                                        ];
                                    } else {
                                        return `Performance: ${contest.performance_rating}`;
                                    }
                                },
                                title: function(context) {
                                    return ratingData[context[0].dataIndex].title;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 0,
                            suggestedMax: 2400,
                            title: {
                                display: true,
                                text: 'Rating'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Contest Date'
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endif
</x-app-layout>
