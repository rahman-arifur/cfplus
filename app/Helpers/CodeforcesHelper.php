<?php

namespace App\Helpers;

class CodeforcesHelper
{
    /**
     * Codeforces rank thresholds and information.
     */
    protected static array $ranks = [
        ['min' => 0, 'max' => 1199, 'name' => 'Newbie', 'color' => 'gray'],
        ['min' => 1200, 'max' => 1399, 'name' => 'Pupil', 'color' => 'green'],
        ['min' => 1400, 'max' => 1599, 'name' => 'Specialist', 'color' => 'cyan'],
        ['min' => 1600, 'max' => 1899, 'name' => 'Expert', 'color' => 'blue'],
        ['min' => 1900, 'max' => 2099, 'name' => 'Candidate Master', 'color' => 'purple'],
        ['min' => 2100, 'max' => 2299, 'name' => 'Master', 'color' => 'orange'],
        ['min' => 2300, 'max' => 2399, 'name' => 'International Master', 'color' => 'orange'],
        ['min' => 2400, 'max' => 2599, 'name' => 'Grandmaster', 'color' => 'red'],
        ['min' => 2600, 'max' => 2999, 'name' => 'International Grandmaster', 'color' => 'red'],
        ['min' => 3000, 'max' => 9999, 'name' => 'Legendary Grandmaster', 'color' => 'red'],
    ];

    /**
     * Get rank information for a given rating.
     *
     * @param int|null $rating
     * @return array
     */
    public static function getRankInfo(?int $rating): array
    {
        if ($rating === null) {
            return [
                'name' => 'Unrated',
                'color' => 'gray',
                'rating' => null,
            ];
        }

        foreach (self::$ranks as $rank) {
            if ($rating >= $rank['min'] && $rating <= $rank['max']) {
                return [
                    'name' => $rank['name'],
                    'color' => $rank['color'],
                    'rating' => $rating,
                ];
            }
        }

        // Fallback for very high ratings
        return [
            'name' => 'Legendary Grandmaster',
            'color' => 'red',
            'rating' => $rating,
        ];
    }

    /**
     * Get rank name for a rating.
     *
     * @param int|null $rating
     * @return string
     */
    public static function getRankName(?int $rating): string
    {
        return self::getRankInfo($rating)['name'];
    }

    /**
     * Get rank color for a rating.
     *
     * @param int|null $rating
     * @return string
     */
    public static function getRankColor(?int $rating): string
    {
        return self::getRankInfo($rating)['color'];
    }

    /**
     * Get Tailwind CSS classes for a rank color.
     *
     * @param string $color
     * @return string
     */
    public static function getTailwindClasses(string $color): string
    {
        return match($color) {
            'gray' => 'text-gray-600',
            'green' => 'text-green-600',
            'cyan' => 'text-cyan-600',
            'blue' => 'text-blue-600',
            'purple' => 'text-purple-600',
            'orange' => 'text-orange-600',
            'red' => 'text-red-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Get background Tailwind CSS classes for a rank color.
     *
     * @param string $color
     * @return string
     */
    public static function getBgClasses(string $color): string
    {
        return match($color) {
            'gray' => 'bg-gray-100 text-gray-800',
            'green' => 'bg-green-100 text-green-800',
            'cyan' => 'bg-cyan-100 text-cyan-800',
            'blue' => 'bg-blue-100 text-blue-800',
            'purple' => 'bg-purple-100 text-purple-800',
            'orange' => 'bg-orange-100 text-orange-800',
            'red' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Format rating with color.
     *
     * @param int|null $rating
     * @return string
     */
    public static function formatRating(?int $rating): string
    {
        if ($rating === null) {
            return 'Unrated';
        }

        return (string) $rating;
    }

    /**
     * Check if rating difference is positive.
     *
     * @param int $oldRating
     * @param int $newRating
     * @return bool
     */
    public static function isRatingIncrease(int $oldRating, int $newRating): bool
    {
        return $newRating > $oldRating;
    }

    /**
     * Get rating change with sign.
     *
     * @param int $oldRating
     * @param int $newRating
     * @return string
     */
    public static function getRatingChange(int $oldRating, int $newRating): string
    {
        $diff = $newRating - $oldRating;
        
        if ($diff > 0) {
            return '+' . $diff;
        }
        
        return (string) $diff;
    }
}
