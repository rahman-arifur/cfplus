<?php

namespace App\View\Components;

use App\Helpers\CodeforcesHelper;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CfRatingBadge extends Component
{
    public ?int $rating;
    public string $rankName;
    public string $rankColor;
    public string $textClasses;
    public string $bgClasses;

    /**
     * Create a new component instance.
     */
    public function __construct(?int $rating = null, public bool $showRank = true)
    {
        $this->rating = $rating;
        $rankInfo = CodeforcesHelper::getRankInfo($rating);
        
        $this->rankName = $rankInfo['name'];
        $this->rankColor = $rankInfo['color'];
        $this->textClasses = CodeforcesHelper::getTailwindClasses($this->rankColor);
        $this->bgClasses = CodeforcesHelper::getBgClasses($this->rankColor);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.cf-rating-badge');
    }
}
