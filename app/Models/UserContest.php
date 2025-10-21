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
}