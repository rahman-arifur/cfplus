<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's Codeforces account.
     */
    public function cfAccount(): HasOne
    {
        return $this->hasOne(CfAccount::class);
    }

    /**
     * Problems attempted or solved by this user.
     */
    public function problems()
    {
        return $this->belongsToMany(Problem::class, 'user_problem')
            ->withPivot(['status', 'solved_at', 'attempts'])
            ->withTimestamps();
    }

    /**
     * Get problems solved by this user.
     */
    public function solvedProblems()
    {
        return $this->belongsToMany(Problem::class, 'user_problem')
            ->wherePivot('status', 'solved')
            ->withPivot(['status', 'solved_at', 'attempts'])
            ->withTimestamps();
    }

    /**
     * Get problems attempted but not solved by this user.
     */
    public function attemptedProblems()
    {
        return $this->belongsToMany(Problem::class, 'user_problem')
            ->wherePivot('status', 'attempted')
            ->withPivot(['status', 'solved_at', 'attempts'])
            ->withTimestamps();
    }

    /**
     * Check if user has solved a problem.
     */
    public function hasSolved($problemId): bool
    {
        return $this->problems()
            ->wherePivot('problem_id', $problemId)
            ->wherePivot('status', 'solved')
            ->exists();
    }

    /**
     * Check if user has attempted a problem.
     */
    public function hasAttempted($problemId): bool
    {
        return $this->problems()
            ->wherePivot('problem_id', $problemId)
            ->exists();
    }
}
