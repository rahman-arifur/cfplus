<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Contest extends Model
{
    protected $fillable = [
        'contest_id',
        'name',
        'type',
        'phase',
        'frozen',
        'duration_seconds',
        'start_time',
        'relative_time',
        'description',
        'difficulty',
        'kind',
        'icpc_region',
        'country',
        'city',
        'season',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'relative_time' => 'datetime',
        'frozen' => 'boolean',
    ];

    /**
     * Scope for upcoming contests.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereIn('phase', ['BEFORE'])
            ->orderBy('start_time', 'asc');
    }

    /**
     * Scope for past contests.
     */
    public function scopePast($query)
    {
        return $query->whereIn('phase', ['FINISHED'])
            ->orderBy('start_time', 'desc');
    }

    /**
     * Scope for running contests.
     */
    public function scopeRunning($query)
    {
        return $query->whereIn('phase', ['CODING', 'PENDING_SYSTEM_TEST', 'SYSTEM_TEST'])
            ->orderBy('start_time', 'desc');
    }

    /**
     * Check if contest is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->phase === 'BEFORE';
    }

    /**
     * Check if contest is running.
     */
    public function isRunning(): bool
    {
        return in_array($this->phase, ['CODING', 'PENDING_SYSTEM_TEST', 'SYSTEM_TEST']);
    }

    /**
     * Check if contest is finished.
     */
    public function isFinished(): bool
    {
        return $this->phase === 'FINISHED';
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Get time until contest starts.
     */
    public function getTimeUntilStartAttribute(): ?string
    {
        if (!$this->start_time || !$this->isUpcoming()) {
            return null;
        }

        return $this->start_time->diffForHumans();
    }
}
