<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomContestParticipation extends Model
{
    protected $fillable = [
        'custom_contest_id',
        'user_id',
        'started_at',
        'finished_at',
        'score',
        'problems_solved',
        'solved_problems',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'score' => 'integer',
        'problems_solved' => 'integer',
        'solved_problems' => 'array',
    ];

    /**
     * Get the custom contest.
     */
    public function customContest(): BelongsTo
    {
        return $this->belongsTo(CustomContest::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if participation is active.
     */
    public function isActive(): bool
    {
        return $this->started_at && !$this->finished_at;
    }

    /**
     * Check if participation is finished.
     */
    public function isFinished(): bool
    {
        return $this->finished_at !== null;
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationInMinutesAttribute(): ?int
    {
        if (!$this->started_at || !$this->finished_at) {
            return null;
        }

        return $this->started_at->diffInMinutes($this->finished_at);
    }
}
