<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CfAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'handle',
        'avatar',
        'country',
        'city',
        'organization',
        'max_rating',
        'current_rating',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the user that owns the Codeforces account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the rating snapshots for this CF account.
     */
    public function ratingSnapshots(): HasMany
    {
        return $this->hasMany(RatingSnapshot::class)->orderBy('rated_at', 'desc');
    }
}
