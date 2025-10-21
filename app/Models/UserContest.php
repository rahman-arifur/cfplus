<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class UserContest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'rating_min',
        'rating_max',
        'problem_count',
        'duration_minutes',
        'status',
        'started_at',
        'completed_at',
        'tags',
        'performance_rating',
        'actual_rating',
        'rating_change',
        'problems_solved',
        'total_score',
    ];

    protected $casts = [
        'tags' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function problems(): BelongsToMany
    {
        return $this->belongsToMany(Problem::class, 'user_contest_problems')
            ->withPivot(['solved_during_contest', 'solved_at'])
            ->withTimestamps();
    }

    public function solvedProblems(): BelongsToMany
    {
        return $this->problems()->wherePivot('solved_during_contest', true);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getRemainingTimeAttribute(): ?int
    {
        if (!$this->isActive() || !$this->started_at) {
            return null;
        }

        $endTime = $this->started_at->addMinutes($this->duration_minutes);
        $now = Carbon::now();

        if ($now->gte($endTime)) {
            return 0;
        }

        return $now->diffInSeconds($endTime);
    }

    public function getRemainingTimeFormattedAttribute(): ?string
    {
        $seconds = $this->remaining_time;
        
        if ($seconds === null) {
            return null;
        }

        if ($seconds <= 0) {
            return '0h 0m 0s';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%dh %dm %ds', $hours, $minutes, $secs);
    }

    public function getProgressAttribute(): int
    {
        $total = $this->problems()->count();
        $solved = $this->solvedProblems()->count();

        return $total > 0 ? round(($solved / $total) * 100) : 0;
    }

    public function start(): bool
    {
        if ($this->isDraft()) {
            $this->update([
                'status' => 'active',
                'started_at' => Carbon::now(),
            ]);
            return true;
        }
        return false;
    }

    public function complete(): bool
    {
        if ($this->isActive()) {
            $this->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
            ]);
            return true;
        }
        return false;
    }

    public function canParticipate(): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        
        $remainingTime = $this->remaining_time;
        return $remainingTime === null || $remainingTime > 0;
    }

    /**
     * Calculate performance rating based on solved problems using Codeforces-style algorithm
     */
    public function calculatePerformanceRating(): int
    {
        $solvedProblems = $this->problems()
            ->wherePivot('solved_during_contest', true)
            ->get();

        if ($solvedProblems->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($solvedProblems as $problem) {
            // Base rating of the problem
            $problemRating = $problem->rating ?? 1000;
            
            // Factor based on solve count (harder if fewer people solved it)
            $solveCount = max($problem->solved_count, 1);
            $difficulty_multiplier = 1 + (1000 / $solveCount); // Fewer solves = higher multiplier
            
            // Time factor: faster solve = higher rating
            $timeFactor = 1.0;
            if ($this->started_at && $problem->pivot->solved_at) {
                $solveTime = Carbon::parse($problem->pivot->solved_at)
                    ->diffInMinutes($this->started_at);
                $contestDuration = $this->duration_minutes;
                
                // Solve in first 25% of contest time = 1.2x bonus
                // Solve in last 25% of contest time = 0.8x penalty
                $timeRatio = $solveTime / $contestDuration;
                if ($timeRatio <= 0.25) {
                    $timeFactor = 1.2;
                } elseif ($timeRatio >= 0.75) {
                    $timeFactor = 0.9;
                }
            }

            // Calculate problem score
            $problemScore = $problemRating * $difficulty_multiplier * $timeFactor;
            
            // Weight is higher for harder problems
            $weight = $problemRating / 1000;
            
            $totalScore += $problemScore * $weight;
            $totalWeight += $weight;
        }

        // Calculate weighted average
        $performanceRating = $totalWeight > 0 ? (int) round($totalScore / $totalWeight) : 0;

        // Bonus for solving multiple problems
        $solvedCount = $solvedProblems->count();
        $completionBonus = min($solvedCount * 10, 100); // Max 100 bonus
        
        return $performanceRating + $completionBonus;
    }

    /**
     * Calculate rating change based on performance and current user rating
     * Uses Codeforces-style Elo rating system with gradual changes
     */
    public function calculateRatingChange(int $currentRating, int $performanceRating): int
    {
        // Calculate the difference between performance and current rating
        $ratingDiff = $performanceRating - $currentRating;
        
        // K-factor determines how quickly rating changes
        // Lower for higher-rated players (more stable), higher for beginners (more volatile)
        if ($currentRating < 1200) {
            $k = 60; // Beginners: rating changes faster
        } elseif ($currentRating < 1400) {
            $k = 50;
        } elseif ($currentRating < 1600) {
            $k = 40;
        } elseif ($currentRating < 1900) {
            $k = 35;
        } elseif ($currentRating < 2100) {
            $k = 30;
        } else {
            $k = 25; // Experts: rating changes slowly
        }
        
        // Apply K-factor: rating moves K% toward performance
        $ratingChange = (int) round($ratingDiff * ($k / 100));
        
        // Cap the maximum change per contest
        $maxChange = 200;
        $ratingChange = max(-$maxChange, min($maxChange, $ratingChange));
        
        return $ratingChange;
    }

    /**
     * Update contest statistics when completed
     */
    public function updateStatistics(): void
    {
        $solvedCount = $this->solvedProblems()->count();
        $performanceRating = $this->calculatePerformanceRating();
        
        // Get user's actual rating from their latest completed contest (by completion time)
        $previousContest = self::where('user_id', $this->user_id)
            ->where('status', 'completed')
            ->where('completed_at', '<', $this->completed_at)
            ->orderBy('completed_at', 'desc')
            ->first();
        
        // Previous actual rating (not performance rating!)
        $currentRating = $previousContest ? 
            ($previousContest->actual_rating ?? 1500) : 1500;
        
        // Calculate rating change based on performance vs current rating
        $ratingChange = $this->calculateRatingChange($currentRating, $performanceRating);
        
        // New actual rating is current rating + change
        $newActualRating = $currentRating + $ratingChange;
        
        $totalScore = $this->problems()
            ->wherePivot('solved_during_contest', true)
            ->sum('rating');

        $this->update([
            'problems_solved' => $solvedCount,
            'performance_rating' => $performanceRating,
            'actual_rating' => $newActualRating,
            'rating_change' => $ratingChange,
            'total_score' => $totalScore,
        ]);
        
        echo "Updated: {$this->title}\n";
        echo "  Performance: {$performanceRating}\n";
        echo "  Previous Rating: {$currentRating}\n";
        echo "  Rating Change: " . ($ratingChange >= 0 ? '+' : '') . "{$ratingChange}\n";
        echo "  New Rating: {$newActualRating}\n\n";
    }
}