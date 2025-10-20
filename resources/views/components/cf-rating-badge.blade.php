<span class="inline-flex items-center gap-2">
    @if($rating !== null)
        <span class="font-semibold {{ $textClasses }}">{{ $rating }}</span>
        @if($showRank)
            <span class="px-2 py-1 text-xs font-medium rounded {{ $bgClasses }}">
                {{ $rankName }}
            </span>
        @endif
    @else
        <span class="text-gray-500">Unrated</span>
    @endif
</span>