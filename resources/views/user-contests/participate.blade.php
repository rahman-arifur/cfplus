<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $userContest->title }}
            </h2>
            <div class="text-right">
                <div class="text-sm text-gray-600 mb-1">Time Remaining</div>
                <div id="timer" class="text-2xl font-bold text-red-600 font-mono" 
                     data-end-time="{{ $userContest->started_at ? $userContest->started_at->addMinutes($userContest->duration_minutes)->timestamp * 1000 : 0 }}">
                    @if($userContest->started_at)
                        --:--:--
                    @else
                        Not Started
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Contest Info -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6 shadow-sm">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Rating Range:</span>
                        <span class="font-medium">{{ $userContest->rating_min }}-{{ $userContest->rating_max }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Problems:</span>
                        <span class="font-medium">{{ $userContest->problem_count }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Duration:</span>
                        <span class="font-medium">{{ floor($userContest->duration_minutes / 60) }}h {{ $userContest->duration_minutes % 60 }}m</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Progress:</span>
                        <span id="progress-display" class="font-medium">{{ $userContest->progress }}%</span>
                    </div>
                </div>
                
                @if($userContest->tags && is_array($userContest->tags) && count($userContest->tags) > 0)
                    <div class="flex flex-wrap gap-1 mt-3">
                        <span class="text-gray-500 text-sm mr-2">Tags:</span>
                        @foreach($userContest->tags as $tag)
                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
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

            <!-- Problems List -->
            <div class="space-y-4">
                @foreach($userContest->problems as $index => $problem)
                    <div class="bg-white border border-gray-200 rounded-lg p-6 hover:border-gray-300 transition duration-200 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-3">
                                    <span class="text-lg font-bold text-gray-500">{{ chr(65 + $index) }}.</span>
                                    <h3 class="text-xl font-semibold">
                                        <a href="{{ $problem->url }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:text-blue-700 transition duration-200">
                                            {{ $problem->name }}
                                        </a>
                                    </h3>
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
                                    
                                    <!-- Solve Status -->
                                    @if($problem->pivot->solved_during_contest)
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded">
                                            ✓ Solved
                                        </span>
                                    @elseif($problem->pivot->solved_at)
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded">
                                            ✓ Previously Solved
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded">
                                            Unsolved
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
                                    <div>
                                        <span class="text-gray-500">Contest:</span>
                                        <span class="font-medium">{{ $problem->contest_id }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Index:</span>
                                        <span class="font-medium">{{ $problem->index }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Solved Count:</span>
                                        <span class="font-medium">{{ number_format($problem->solved_count) }}</span>
                                    </div>
                                    @if($problem->pivot->solved_during_contest && $problem->pivot->solved_at)
                                        <div>
                                            <span class="text-gray-500">Solved At:</span>
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($problem->pivot->solved_at)->format('H:i:s') }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                @if($problem->tags && is_array($problem->tags) && count($problem->tags) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($problem->tags as $tag)
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex flex-col space-y-2 ml-4">
                                <a href="{{ $problem->url }}" 
                                   target="_blank"
                                   class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded text-center transition duration-200">
                                    Open Problem
                                </a>
                                
                                @if(!$problem->pivot->solved_during_contest)
                                    <form action="{{ route('user-contests.update-problem-status', [$userContest, $problem]) }}" 
                                          method="POST" 
                                          class="mark-solved-form">
                                        @csrf
                                        <input type="hidden" name="solved" value="1">
                                        <button type="submit" 
                                                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded transition duration-200">
                                            Mark as Solved
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('user-contests.update-problem-status', [$userContest, $problem]) }}" 
                                          method="POST" 
                                          class="mark-unsolved-form">
                                        @csrf
                                        <input type="hidden" name="solved" value="0">
                                        <button type="submit" 
                                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded transition duration-200">
                                            Mark as Unsolved
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Contest Actions -->
            <div class="mt-8 text-center space-x-4 flex flex-wrap justify-center gap-4">
                <button id="syncButton" 
                        type="button"
                        onclick="syncWithCodeforces()"
                        style="background-color: #2563eb !important; color: white !important;"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                    🔄 Sync from Codeforces
                </button>

                <form action="{{ route('user-contests.complete', $userContest) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('Are you sure you want to end this contest early? This action cannot be undone.')">
                    @csrf
                    <button type="submit" 
                            style="background-color: #dc2626 !important; color: white !important;"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                        🛑 End Contest Early
                    </button>
                </form>
                
                <a href="{{ route('user-contests.index') }}" 
                   style="background-color: #374151 !important; color: white !important;"
                   class="inline-block bg-gray-700 hover:bg-gray-800 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg border border-gray-600">
                    ← Back to Contests
                </a>
            </div>
        </div>
    </div>

    <script>
        // Timer functionality
        function updateTimer() {
            const timerElement = document.getElementById('timer');
            const endTime = parseInt(timerElement.getAttribute('data-end-time'));
            const now = new Date().getTime();
            const timeLeft = endTime - now;

            if (timeLeft <= 0) {
                // Contest ended
                timerElement.textContent = "00:00:00";
                timerElement.classList.remove('text-red-600');
                timerElement.classList.add('text-gray-500');
                
                // Auto-submit contest completion
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("user-contests.complete", $userContest) }}';
                form.innerHTML = `
                    @csrf
                `;
                document.body.appendChild(form);
                form.submit();
                
                return;
            }

            // Calculate time components
            const hours = Math.floor(timeLeft / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

            // Format with leading zeros
            const formattedTime = hours.toString().padStart(2, '0') + ':' + 
                                  minutes.toString().padStart(2, '0') + ':' + 
                                  seconds.toString().padStart(2, '0');
            timerElement.textContent = formattedTime;

            // Change color based on time remaining
            if (timeLeft < 10 * 60 * 1000) { // Less than 10 minutes
                timerElement.classList.remove('text-red-600', 'text-yellow-600');
                timerElement.classList.add('text-red-700', 'animate-pulse');
            } else if (timeLeft < 30 * 60 * 1000) { // Less than 30 minutes
                timerElement.classList.remove('text-red-600', 'text-red-700', 'animate-pulse');
                timerElement.classList.add('text-yellow-600');
            }
        }

        // Update timer every second
        updateTimer(); // Initial call
        setInterval(updateTimer, 1000);

        // Progress calculation and update
        function updateProgress() {
            const totalProblems = {{ $userContest->problem_count }};
            const solvedProblems = document.querySelectorAll('.mark-unsolved-form').length;
            const progress = Math.round((solvedProblems / totalProblems) * 100);
            document.getElementById('progress-display').textContent = progress + '%';
        }

        // Handle form submissions with AJAX for better UX
        document.querySelectorAll('.mark-solved-form, .mark-unsolved-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                console.log('Form submit intercepted', this.action);
                
                const formData = new FormData(this);
                const button = this.querySelector('button');
                const originalText = button.textContent;
                
                button.disabled = true;
                button.textContent = 'Updating...';
                
                console.log('Sending AJAX request to:', this.action);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    console.log('Response received:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        // Reload the page to update the UI
                        window.location.reload();
                    } else {
                        alert('Error updating problem status: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating problem status. Please try again.');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = originalText;
                });
            });
        });

        // Sync with Codeforces function
        function syncWithCodeforces() {
            const syncButton = document.getElementById('syncButton');
            const originalText = syncButton.textContent;
            
            syncButton.disabled = true;
            syncButton.textContent = '⏳ Syncing...';
            
            console.log('Starting sync...');
            
            fetch('{{ route("user-contests.sync-status", $userContest) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                console.log('Success response:', data);
                if (data.success) {
                    // Show success message with better formatting
                    syncButton.textContent = '✅ ' + data.message;
                    syncButton.style.backgroundColor = '#16a34a';
                    
                    // Reload after 1 second to show the success state briefly
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + (data.message || 'Failed to sync'));
                    syncButton.disabled = false;
                    syncButton.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Sync error:', error);
                const errorMsg = error.message || (typeof error === 'object' && error.message) || 'Failed to sync with Codeforces';
                alert(errorMsg + '. Please check console for details.');
                syncButton.disabled = false;
                syncButton.textContent = originalText;
            });
        }

        // Auto-save functionality (optional - save progress every 30 seconds)
        setInterval(function() {
            // This could be used to auto-save contest state
            console.log('Auto-save checkpoint');
        }, 30000);
    </script>
</x-app-layout>