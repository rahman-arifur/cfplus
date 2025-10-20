<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingSnapshot extends Model
{
    protected $fillable = [
        'cf_account_id',
        'contest_id',
        'contest_name',
        'rank',
        'old_rating',
        'new_rating',
        'rated_at',
    ];

    protected $casts = [
        'rated_at' => 'datetime',
    ];

    /**
     * Get the CF account that owns this rating snapshot.
     */
    public function cfAccount(): BelongsTo
    {
        return $this->belongsTo(CfAccount::class);
    }

    /**
     * Get the rating change for this contest.
     */
    public function getRatingChangeAttribute(): int
    {
        return $this->new_rating - $this->old_rating;
    }
}
