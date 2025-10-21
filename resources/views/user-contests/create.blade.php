<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Custom Contest') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6">
                        <p class="text-gray-600">Set up a practice contest with problems matching your criteria</p>
                    </div>

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <h3 class="font-semibold text-red-800 mb-2">Please fix the following errors:</h3>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li class="text-red-700">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('user-contests.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Contest Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Contest Title</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
                                   placeholder="e.g., Rating 1200-1400 Practice Contest"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                        </div>

                        <!-- Rating Range -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="rating_min" class="block text-sm font-medium text-gray-700 mb-2">Minimum Rating</label>
                                <input type="number" 
                                       id="rating_min" 
                                       name="rating_min" 
                                       value="{{ old('rating_min', 800) }}"
                                       min="800" 
                                       max="3500" 
                                       step="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       required>
                            </div>
                            <div>
                                <label for="rating_max" class="block text-sm font-medium text-gray-700 mb-2">Maximum Rating</label>
                                <input type="number" 
                                       id="rating_max" 
                                       name="rating_max" 
                                       value="{{ old('rating_max', 1200) }}"
                                       min="800" 
                                       max="3500" 
                                       step="100"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       required>
                            </div>
                        </div>

                        <!-- Problem Count and Duration -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="problem_count" class="block text-sm font-medium text-gray-700 mb-2">Number of Problems</label>
                                <select id="problem_count" 
                                        name="problem_count"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required>
                                    <option value="3" {{ old('problem_count') == 3 ? 'selected' : '' }}>3 Problems</option>
                                    <option value="4" {{ old('problem_count') == 4 ? 'selected' : '' }}>4 Problems</option>
                                    <option value="5" {{ old('problem_count', 5) == 5 ? 'selected' : '' }}>5 Problems</option>
                                    <option value="6" {{ old('problem_count') == 6 ? 'selected' : '' }}>6 Problems</option>
                                    <option value="7" {{ old('problem_count') == 7 ? 'selected' : '' }}>7 Problems</option>
                                    <option value="8" {{ old('problem_count') == 8 ? 'selected' : '' }}>8 Problems</option>
                                </select>
                            </div>
                            <div>
                                <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Duration</label>
                                <select id="duration_minutes" 
                                        name="duration_minutes"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required>
                                    <option value="60" {{ old('duration_minutes') == 60 ? 'selected' : '' }}>1 hour</option>
                                    <option value="90" {{ old('duration_minutes') == 90 ? 'selected' : '' }}>1.5 hours</option>
                                    <option value="120" {{ old('duration_minutes', 120) == 120 ? 'selected' : '' }}>2 hours</option>
                                    <option value="150" {{ old('duration_minutes') == 150 ? 'selected' : '' }}>2.5 hours</option>
                                    <option value="180" {{ old('duration_minutes') == 180 ? 'selected' : '' }}>3 hours</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">Tags (optional)</label>
                            <input type="text" 
                                   id="tags" 
                                   name="tags" 
                                   value="{{ old('tags') }}"
                                   placeholder="e.g., dp, greedy, math (comma-separated)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-gray-500 text-sm mt-1">Leave empty to include problems with any tags. Use comma-separated values to filter by specific tags.</p>
                        </div>

                        <!-- Preview Info -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h3 class="font-semibold mb-2 text-blue-600">Contest Preview</h3>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Rating Range:</span>
                                    <span id="preview-rating" class="font-medium">800-1200</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Problems:</span>
                                    <span id="preview-problems" class="font-medium">5</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Duration:</span>
                                    <span id="preview-duration" class="font-medium">2 hours</span>
                                </div>
                                <div>
                                    <span class="text-gray-600">Status:</span>
                                    <span class="text-yellow-600 font-medium">Draft</span>
                                </div>
                            </div>
                            <p class="text-gray-500 text-xs mt-2">The contest will be saved as a draft. You can start it from your contests list when ready.</p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex flex-wrap gap-4 pt-4">
                            <button type="submit" 
                                    name="action" 
                                    value="create_and_start"
                                    style="background-color: #16a34a !important; color: white !important;"
                                    class="flex-1 min-w-[200px] bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg border-2 border-green-700">
                                🚀 Create & Start Contest
                            </button>
                            <button type="submit" 
                                    name="action" 
                                    value="create_draft"
                                    style="background-color: #2563eb !important; color: white !important;"
                                    class="flex-1 min-w-[200px] bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-200 shadow-md hover:shadow-lg border-2 border-blue-700">
                                💾 Save as Draft
                            </button>
                        </div>

                        <div class="text-center mt-4">
                            <a href="{{ route('user-contests.index') }}" 
                               style="background-color: #374151 !important; color: white !important;"
                               class="inline-block bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition duration-200 shadow-sm hover:shadow-md">
                                ← Cancel
                            </a>
                        </div>
                                ← Back to My Contests
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live preview updates
        function updatePreview() {
            const ratingMin = document.getElementById('rating_min').value;
            const ratingMax = document.getElementById('rating_max').value;
            const problemCount = document.getElementById('problem_count').value;
            const duration = document.getElementById('duration_minutes').value;
            
            document.getElementById('preview-rating').textContent = `${ratingMin}-${ratingMax}`;
            document.getElementById('preview-problems').textContent = problemCount;
            
            const hours = Math.floor(duration / 60);
            const minutes = duration % 60;
            let durationText = '';
            if (hours > 0) {
                durationText += `${hours} hour${hours > 1 ? 's' : ''}`;
            }
            if (minutes > 0) {
                if (hours > 0) durationText += ' ';
                durationText += `${minutes} min`;
            }
            document.getElementById('preview-duration').textContent = durationText;
        }
        
        // Add event listeners
        document.getElementById('rating_min').addEventListener('input', updatePreview);
        document.getElementById('rating_max').addEventListener('input', updatePreview);
        document.getElementById('problem_count').addEventListener('change', updatePreview);
        document.getElementById('duration_minutes').addEventListener('change', updatePreview);
        
        // Rating validation
        document.getElementById('rating_min').addEventListener('input', function() {
            const min = parseInt(this.value);
            const maxInput = document.getElementById('rating_max');
            const max = parseInt(maxInput.value);
            
            if (min >= max) {
                maxInput.value = Math.min(3500, min + 100);
                updatePreview();
            }
        });
        
        document.getElementById('rating_max').addEventListener('input', function() {
            const max = parseInt(this.value);
            const minInput = document.getElementById('rating_min');
            const min = parseInt(minInput.value);
            
            if (max <= min) {
                minInput.value = Math.max(800, max - 100);
                updatePreview();
            }
        });
    </script>
</x-app-layout>