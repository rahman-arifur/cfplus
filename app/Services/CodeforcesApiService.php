<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodeforcesApiService
{
    protected string $apiBase;
    protected int $cacheTtl;
    protected int $rateLimitDelay;
    protected int $timeout;
    protected static ?float $lastRequestTime = null;

    public function __construct()
    {
        $this->apiBase = config('services.codeforces.api_base');
        $this->cacheTtl = config('services.codeforces.cache_ttl');
        $this->rateLimitDelay = config('services.codeforces.rate_limit_delay');
        $this->timeout = config('services.codeforces.timeout');
    }

    /**
     * Get user information by handle.
     *
     * @param string $handle
     * @return array|null
     */
    public function getUserInfo(string $handle): ?array
    {
        $cacheKey = "cf_user_info_{$handle}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($handle) {
            $response = $this->makeRequest('user.info', [
                'handles' => $handle,
            ]);

            if ($response && $response['status'] === 'OK' && !empty($response['result'])) {
                return $response['result'][0];
            }

            return null;
        });
    }

    /**
     * Get user rating history by handle.
     *
     * @param string $handle
     * @return array
     */
    public function getUserRating(string $handle): array
    {
        $cacheKey = "cf_user_rating_{$handle}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($handle) {
            $response = $this->makeRequest('user.rating', [
                'handle' => $handle,
            ]);

            if ($response && $response['status'] === 'OK') {
                return $response['result'] ?? [];
            }

            return [];
        });
    }

    /**
     * Get user submission status by handle.
     *
     * @param string $handle
     * @param int $count Maximum number of submissions to retrieve
     * @return array
     */
    public function getUserStatus(string $handle, int $count = 100): array
    {
        $cacheKey = "cf_user_status_{$handle}_{$count}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($handle, $count) {
            $response = $this->makeRequest('user.status', [
                'handle' => $handle,
                'from' => 1,
                'count' => $count,
            ]);

            if ($response && $response['status'] === 'OK') {
                return $response['result'] ?? [];
            }

            return [];
        });
    }

    /**
     * Get all contests.
     *
     * @param bool $gym Include gym contests
     * @return array
     */
    public function getContests(bool $gym = false): array
    {
        $cacheKey = "cf_contests_" . ($gym ? 'with_gym' : 'no_gym');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($gym) {
            $response = $this->makeRequest('contest.list', [
                'gym' => $gym ? 'true' : 'false',
            ]);

            if ($response && $response['status'] === 'OK') {
                return $response['result'] ?? [];
            }

            return [];
        });
    }

    /**
     * Get problemset with optional filters.
     *
     * @param array $tags Problem tags to filter by
     * @return array
     */
    public function getProblemset(array $tags = []): array
    {
        $tagsString = implode(';', $tags);
        $cacheKey = "cf_problemset_" . md5($tagsString);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($tags) {
            $params = [];
            if (!empty($tags)) {
                $params['tags'] = implode(';', $tags);
            }

            $response = $this->makeRequest('problemset.problems', $params);

            if ($response && $response['status'] === 'OK') {
                return $response['result'] ?? [];
            }

            return [];
        });
    }

    /**
     * Make HTTP request to Codeforces API with rate limiting.
     *
     * @param string $method API method name
     * @param array $params Query parameters
     * @return array|null
     */
    protected function makeRequest(string $method, array $params = []): ?array
    {
        // Rate limiting: ensure minimum delay between requests
        $this->enforceRateLimit();

        $url = "{$this->apiBase}/{$method}";

        try {
            Log::info("Codeforces API request", [
                'method' => $method,
                'params' => $params,
            ]);

            $response = Http::timeout($this->timeout)
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['status']) && $data['status'] === 'OK') {
                    Log::info("Codeforces API success", ['method' => $method]);
                    return $data;
                }

                Log::warning("Codeforces API returned non-OK status", [
                    'method' => $method,
                    'status' => $data['status'] ?? 'unknown',
                    'comment' => $data['comment'] ?? 'no comment',
                ]);

                return null;
            }

            Log::error("Codeforces API request failed", [
                'method' => $method,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error("Codeforces API exception", [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Enforce rate limiting between API requests.
     *
     * @return void
     */
    protected function enforceRateLimit(): void
    {
        if (self::$lastRequestTime !== null) {
            $timeSinceLastRequest = microtime(true) - self::$lastRequestTime;
            $requiredDelay = $this->rateLimitDelay;

            if ($timeSinceLastRequest < $requiredDelay) {
                $sleepTime = $requiredDelay - $timeSinceLastRequest;
                usleep((int) ($sleepTime * 1000000)); // Convert to microseconds
            }
        }

        self::$lastRequestTime = microtime(true);
    }

    /**
     * Clear cache for a specific handle.
     *
     * @param string $handle
     * @return void
     */
    public function clearCacheForHandle(string $handle): void
    {
        Cache::forget("cf_user_info_{$handle}");
        Cache::forget("cf_user_rating_{$handle}");
        Cache::forget("cf_user_status_{$handle}_100");
        
        Log::info("Cleared Codeforces cache for handle", ['handle' => $handle]);
    }

    /**
     * Clear all Codeforces-related cache.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        // This is a simplified approach - in production, you might want
        // to use cache tags or a more sophisticated cache management strategy
        Cache::flush();
        
        Log::info("Cleared all Codeforces cache");
    }
}
