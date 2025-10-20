<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Problem extends Model
{
    protected $fillable = [
        'contest_id',
        'index',
        'code',
        'name',
        'rating',
        'tags',
        'type',
        'solved_count',
    ];

    protected $casts = [
        'tags' => 'array',
        'rating' => 'integer',
        'contest_id' => 'integer',
        'solved_count' => 'integer',
    ];

    /**
     * Get the full problem code (e.g., "1690A").
     */
    public function getFullCodeAttribute(): string
    {
        return $this->code;
    }

    /**
     * Get Codeforces problem URL.
     */
    public function getUrlAttribute(): string
    {
        if ($this->contest_id) {
            return "https://codeforces.com/contest/{$this->contest_id}/problem/{$this->index}";
        }
        return "https://codeforces.com/problemset/problem/{$this->code}";
    }

    /**
     * Get rating color based on difficulty.
     */
    public function getRatingColorAttribute(): string
    {
        if (!$this->rating) return 'gray';
        
        return match(true) {
            $this->rating < 1200 => 'gray',
            $this->rating < 1400 => 'green',
            $this->rating < 1600 => 'cyan',
            $this->rating < 1900 => 'blue',
            $this->rating < 2100 => 'purple',
            $this->rating < 2400 => 'orange',
            default => 'red'
        };
    }

    /**
     * Scope for filtering by rating range.
     */
    public function scopeRatingBetween($query, $min, $max)
    {
        return $query->whereBetween('rating', [$min, $max]);
    }

    /**
     * Scope for filtering by tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }
        return $query;
    }

    /**
     * Users who have attempted or solved this problem.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_problem')
            ->withPivot(['status', 'solved_at', 'attempts'])
            ->withTimestamps();
    }

    /**
     * Check if a user has solved this problem.
     */
    public function isSolvedBy(?User $user): bool
    {
        if (!$user) return false;
        
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivot('status', 'solved')
            ->exists();
    }

    /**
     * Check if a user has attempted this problem.
     */
    public function isAttemptedBy(?User $user): bool
    {
        if (!$user) return false;
        
        return $this->users()
            ->wherePivot('user_id', $user->id)
            ->exists();
    }
}
