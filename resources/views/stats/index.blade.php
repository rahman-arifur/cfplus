<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Statistics') }} - {{ $cfAccount->handle }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Rating Graph -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rating Progress</h3>
                    
                    @if($ratingHistory->count() > 0)
                        <div class="h-96">
                            <canvas id="ratingChart"></canvas>
                        </div>
                    @else
                        <p class="text-gray-600">No rating history available. Sync your account to fetch rating data.</p>
                    @endif
                </div>
            </div>

            <!-- Problem Statistics as Pie Chart -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Problem Statistics</h3>
                    
                    @php
                        $solved = $problemStats['solved_count'] ?? 0;
                        $attempted = $problemStats['attempted_count'] ?? 0;
                        $unsolved = $attempted > $solved ? $attempted - $solved : 0;
                    @endphp
                    
                    @if($solved > 0 || $attempted > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Pie Chart -->
                            <div class="h-80">
                                <canvas id="problemStatsChart"></canvas>
                            </div>
                            
                            <!-- Statistics Summary -->
                            <div class="flex flex-col justify-center space-y-4">
                                <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                                    <div>
                                        <div class="text-sm text-gray-600">Solved Problems</div>
                                        <div class="text-2xl font-bold text-green-600">{{ number_format($solved) }}</div>
                                    </div>
                                    <div class="w-12 h-12 bg-green-500 rounded-full"></div>
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg">
                                    <div>
                                        <div class="text-sm text-gray-600">Attempted (Unsolved)</div>
                                        <div class="text-2xl font-bold text-yellow-600">{{ number_format($unsolved) }}</div>
                                    </div>
                                    <div class="w-12 h-12 bg-yellow-500 rounded-full"></div>
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                    <div>
                                        <div class="text-sm text-gray-600">Total Attempted</div>
                                        <div class="text-2xl font-bold text-blue-600">{{ number_format($attempted) }}</div>
                                    </div>
                                    <div class="w-12 h-12 bg-blue-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-600">No problem statistics available. Sync your account to fetch data.</p>
                    @endif
                </div>
            </div>

            <!-- Problem Tags -->
            @if(!empty($problemStats['tag_counts']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Problem Tags (Solved Problems)</h3>
                    <div class="space-y-3">
                        @php
                            $sortedTags = $problemStats['tag_counts'];
                            arsort($sortedTags);
                            $topTags = array_slice($sortedTags, 0, 10, true);
                            $maxTagCount = !empty($topTags) ? max($topTags) : 1;
                        @endphp
                        @foreach($topTags as $tag => $count)
                            @php
                                $percentage = $maxTagCount > 0 ? ($count / $maxTagCount) * 100 : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-40 flex-shrink-0">
                                    <span class="text-sm font-medium text-gray-700 capitalize">{{ $tag }}</span>
                                </div>
                                <div class="flex-1 bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300" 
                                         style="width: {{ $percentage }}%; background-color: #6366f1;">
                                    </div>
                                </div>
                                <div class="w-24 text-right flex-shrink-0">
                                    <span class="text-sm font-semibold text-gray-900">{{ number_format($count) }}</span>
                                    <span class="text-xs text-gray-500 ml-1">({{ number_format(($count / ($problemStats['solved_count'] ?: 1)) * 100, 1) }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Programming Languages -->
            @if(!empty($problemStats['language_counts']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Programming Languages</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Chart -->
                        <div class="h-56 flex items-center justify-center">
                            <canvas id="languagesChart"></canvas>
                        </div>
                        <!-- Legend/Stats -->
                        <div class="flex flex-col justify-center space-y-3">
                            @php
                                $totalLangSubmissions = array_sum($problemStats['language_counts']);
                                $sortedLanguages = $problemStats['language_counts'];
                                arsort($sortedLanguages);
                                $topLanguages = array_slice($sortedLanguages, 0, 5, true);
                                $langColors = ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ef4444'];
                                $langIndex = 0;
                            @endphp
                            @foreach($topLanguages as $language => $count)
                                @php
                                    $percentage = $totalLangSubmissions > 0 ? ($count / $totalLangSubmissions) * 100 : 0;
                                    $color = $langColors[$langIndex] ?? '#6b7280';
                                    $langIndex++;
                                @endphp
                                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $language }}</div>
                                        <div class="text-xs text-gray-500">{{ number_format($count) }} submissions</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-gray-900">{{ number_format($percentage, 1) }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Submission Verdicts -->
            @if(!empty($problemStats['verdict_counts']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Submission Verdicts</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Chart -->
                        <div class="h-56 flex items-center justify-center">
                            <canvas id="verdictsChart"></canvas>
                        </div>
                        <!-- Legend/Stats -->
                        <div class="flex flex-col justify-center space-y-3">
                            @php
                                $totalSubmissions = $problemStats['total_submissions'] ?? array_sum($problemStats['verdict_counts']);
                                $sortedVerdicts = $problemStats['verdict_counts'];
                                arsort($sortedVerdicts);
                                $verdictColors = [
                                    'OK' => '#10b981',
                                    'WRONG_ANSWER' => '#ef4444',
                                    'TIME_LIMIT_EXCEEDED' => '#f59e0b',
                                    'RUNTIME_ERROR' => '#f97316',
                                    'COMPILATION_ERROR' => '#8b5cf6',
                                    'MEMORY_LIMIT_EXCEEDED' => '#ec4899',
                                    'CHALLENGED' => '#64748b',
                                    'SKIPPED' => '#94a3b8',
                                    'TESTING' => '#06b6d4',
                                    'REJECTED' => '#dc2626',
                                ];
                            @endphp
                            @foreach($sortedVerdicts as $verdict => $count)
                                @php
                                    $percentage = $totalSubmissions > 0 ? ($count / $totalSubmissions) * 100 : 0;
                                    $color = $verdictColors[$verdict] ?? '#3b82f6';
                                    $displayName = str_replace('_', ' ', $verdict);
                                @endphp
                                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-4 h-4 rounded-full flex-shrink-0" style="background-color: {{ $color }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $displayName }}</div>
                                        <div class="text-xs text-gray-500">{{ number_format($count) }} submissions</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-gray-900">{{ number_format($percentage, 1) }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    
    @if($ratingHistory->count() > 0)
    <!-- Rating Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('ratingChart');
            if (ctx) {
                const ratingData = @json($ratingHistory);
                
                new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        datasets: [{
                            label: 'Rating',
                            data: ratingData.map(item => ({
                                x: item.timestamp,
                                y: item.rating
                            })),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    title: function(context) {
                                        const index = context[0].dataIndex;
                                        return ratingData[index].contest_name;
                                    },
                                    label: function(context) {
                                        const index = context.dataIndex;
                                        const rating = ratingData[index].rating;
                                        const change = ratingData[index].change;
                                        const changeStr = change >= 0 ? '+' + change : change;
                                        return `Rating: ${rating} (${changeStr})`;
                                    },
                                    afterLabel: function(context) {
                                        const index = context.dataIndex;
                                        return `Date: ${ratingData[index].date}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'month',
                                    displayFormats: {
                                        month: 'MMM yyyy'
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Rating'
                                },
                                ticks: {
                                    stepSize: 100
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif
    
    @php
        $solved = $problemStats['solved_count'] ?? 0;
        $attempted = $problemStats['attempted_count'] ?? 0;
        $unsolved = $attempted > $solved ? $attempted - $solved : 0;
    @endphp
    
    @if($solved > 0 || $attempted > 0)
    <!-- Pie Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pieCtx = document.getElementById('problemStatsChart');
            if (pieCtx) {
                new Chart(pieCtx.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: ['Solved', 'Attempted (Unsolved)'],
                        datasets: [{
                            data: [{{ $solved }}, {{ $unsolved }}],
                            backgroundColor: [
                                'rgb(34, 197, 94)',  // green-500
                                'rgb(234, 179, 8)',   // yellow-500
                            ],
                            borderColor: [
                                'rgb(22, 163, 74)',  // green-600
                                'rgb(202, 138, 4)',   // yellow-600
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = {{ $attempted }};
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif

    @if(!empty($problemStats['language_counts']))
    <!-- Languages Donut Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const langCtx = document.getElementById('languagesChart');
            if (langCtx) {
                @php
                    $totalLangSubmissions = array_sum($problemStats['language_counts']);
                    $sortedLanguages = $problemStats['language_counts'];
                    arsort($sortedLanguages);
                    $topLanguages = array_slice($sortedLanguages, 0, 5, true);
                @endphp
                
                new Chart(langCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode(array_keys($topLanguages)) !!},
                        datasets: [{
                            data: {!! json_encode(array_values($topLanguages)) !!},
                            backgroundColor: [
                                '#10b981',  // green
                                '#3b82f6',  // blue
                                '#8b5cf6',  // purple
                                '#f59e0b',  // amber
                                '#ef4444',  // red
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = {{ $totalLangSubmissions }};
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif

    @if(!empty($problemStats['verdict_counts']))
    <!-- Verdicts Donut Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const verdictCtx = document.getElementById('verdictsChart');
            if (verdictCtx) {
                @php
                    $sortedVerdicts = $problemStats['verdict_counts'];
                    arsort($sortedVerdicts);
                    $verdictLabels = array_map(fn($v) => str_replace('_', ' ', $v), array_keys($sortedVerdicts));
                    $verdictValues = array_values($sortedVerdicts);
                    $verdictColorMap = [
                        'OK' => '#10b981',
                        'WRONG_ANSWER' => '#ef4444',
                        'TIME_LIMIT_EXCEEDED' => '#f59e0b',
                        'RUNTIME_ERROR' => '#f97316',
                        'COMPILATION_ERROR' => '#8b5cf6',
                        'MEMORY_LIMIT_EXCEEDED' => '#ec4899',
                        'CHALLENGED' => '#64748b',
                        'SKIPPED' => '#94a3b8',
                        'TESTING' => '#06b6d4',
                        'REJECTED' => '#dc2626',
                    ];
                    $verdictColors = array_map(fn($v) => $verdictColorMap[$v] ?? '#3b82f6', array_keys($sortedVerdicts));
                @endphp
                
                new Chart(verdictCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($verdictLabels) !!},
                        datasets: [{
                            data: {!! json_encode($verdictValues) !!},
                            backgroundColor: {!! json_encode($verdictColors) !!},
                            borderColor: '#ffffff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
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
