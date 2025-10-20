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

            <!-- Problem Statistics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Submissions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Total Submissions</div>
                        <div class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($problemStats['total_submissions']) }}
                        </div>
                    </div>
                </div>

                <!-- Solved Problems -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Solved Problems</div>
                        <div class="text-3xl font-bold text-green-600 mt-2">
                            {{ number_format($problemStats['solved_count']) }}
                        </div>
                    </div>
                </div>

                <!-- Attempted Problems -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">Attempted Problems</div>
                        <div class="text-3xl font-bold text-blue-600 mt-2">
                            {{ number_format($problemStats['attempted_count']) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verdict Distribution -->
            @if(!empty($problemStats['verdict_counts']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Submission Verdicts</h3>
                    <div class="space-y-2">
                        @foreach($problemStats['verdict_counts'] as $verdict => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ $verdict }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-48 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" 
                                             style="width: {{ ($count / $problemStats['total_submissions']) * 100 }}%">
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-600 w-16 text-right">{{ number_format($count) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Top Problem Tags -->
            @if(!empty($problemStats['tag_counts']))
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Problem Tags</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($problemStats['tag_counts'] as $tag => $count)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $tag }}
                                <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-xs bg-blue-200">{{ $count }}</span>
                            </span>
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
                    <div class="space-y-2">
                        @php
                            $totalLangSubmissions = array_sum($problemStats['language_counts']);
                            $sortedLanguages = $problemStats['language_counts'];
                            arsort($sortedLanguages);
                            $topLanguages = array_slice($sortedLanguages, 0, 5, true);
                        @endphp
                        @foreach($topLanguages as $language => $count)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">{{ $language }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-48 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" 
                                             style="width: {{ ($count / $totalLangSubmissions) * 100 }}%">
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-600 w-16 text-right">{{ number_format($count) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    @if($ratingHistory->count() > 0)
    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('ratingChart').getContext('2d');
            
            const ratingData = @json($ratingHistory);
            
            new Chart(ctx, {
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
        });
    </script>
    @endif
</x-app-layout>
