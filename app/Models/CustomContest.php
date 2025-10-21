<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomContest extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_at',
        'duration_minutes',
        'include_in_stats',
        'is_public',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'include_in_stats' => 'boolean',
        'is_public' => 'boolean',
        'duration_minutes' => 'integer',
    ];

    /**
     * Get the user who created this contest.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the problems in this contest.
     */
    public function problems(): BelongsToMany
    {
        return $this->belongsToMany(Problem::class, 'custom_contest_problems')
            ->withPivot('points', 'order_index')
            ->withTimestamps()
            ->orderByPivot('order_index');
    }

    /**
     * Get the participations for this contest.
     */
    public function participations(): HasMany
    {
        return $this->hasMany(CustomContestParticipation::class);
    }

    /**
     * Check if contest is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if contest has started.
     */
    public function hasStarted(): bool
    {
        return $this->start_at && $this->start_at->isPast();
    }

    /**
     * Check if contest has ended.
     */
    public function hasEnded(): bool
    {
        if (!$this->start_at) {
            return false;
        }
        
        $endTime = $this->start_at->copy()->addMinutes($this->duration_minutes);
        return $endTime->isPast();
    }

    /**
     * Get remaining time in minutes.
     */
    public function getRemainingTimeAttribute(): ?int
    {
        if (!$this->start_at || !$this->isActive()) {
            return null;
        }

        $endTime = $this->start_at->copy()->addMinutes($this->duration_minutes);
        $now = now();

        if ($now->greaterThan($endTime)) {
            return 0;
        }

        return $now->diffInMinutes($endTime);
    }

    /**
     * Scope for user's contests.
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for draft contests.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for active contests.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for completed contests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
