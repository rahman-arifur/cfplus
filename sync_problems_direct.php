<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Starting problem sync...\n";

try {
    echo "Fetching from Codeforces API (bypassing cache)...\n";
    // Fetch directly from API without caching
    $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://codeforces.com/api/problemset.problems');
    
    if (!$response->successful()) {
        echo "ERROR: API request failed with status " . $response->status() . "\n";
        exit(1);
    }
    
    $apiResult = $response->json();
    
    if (!isset($apiResult['status']) || $apiResult['status'] !== 'OK') {
        echo "ERROR: API returned error status\n";
        exit(1);
    }
    
    $data = $apiResult['result'];
    
    if (empty($data['problems'])) {
        echo "ERROR: No problems received\n";
        exit(1);
    }
    
    $problems = $data['problems'];
    $problemStatistics = $data['problemStatistics'] ?? [];
    
    echo "Found " . count($problems) . " problems\n";
    echo "Found " . count($problemStatistics) . " statistics\n";
    
    // Create solved counts map
    $solvedCounts = [];
    foreach ($problemStatistics as $stat) {
        $key = $stat['contestId'] . '_' . $stat['index'];
        $solvedCounts[$key] = $stat['solvedCount'] ?? 0;
    }
    
    $syncedCount = 0;
    $skippedCount = 0;
    $total = count($problems);
    
    echo "Starting sync...\n";
    
    foreach (array_chunk($problems, 100) as $chunkIndex => $problemChunk) {
        foreach ($problemChunk as $problem) {
            $contestId = $problem['contestId'] ?? null;
            $index = $problem['index'] ?? null;
            
            if (!$contestId || !$index) {
                $skippedCount++;
                continue;
            }
            
            $code = $contestId . $index;
            $key = $contestId . '_' . $index;
            
            \App\Models\Problem::updateOrCreate(
                ['code' => $code],
                [
                    'contest_id' => $contestId,
                    'index' => $index,
                    'name' => $problem['name'] ?? 'Unknown',
                    'rating' => $problem['rating'] ?? null,
                    'tags' => $problem['tags'] ?? [],
                    'type' => $problem['type'] ?? 'PROGRAMMING',
                    'solved_count' => $solvedCounts[$key] ?? 0,
                ]
            );
            
            $syncedCount++;
        }
        
        if (($chunkIndex + 1) % 10 == 0) {
            $progress = round(($syncedCount / $total) * 100, 1);
            echo "Progress: {$syncedCount}/{$total} ({$progress}%)\n";
        }
    }
    
    echo "\n✓ Successfully synced {$syncedCount} problems!\n";
    if ($skippedCount > 0) {
        echo "⚠ Skipped {$skippedCount} problems\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
