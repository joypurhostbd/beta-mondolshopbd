<div class="inline-flex items-baseline space-x-2">
    <span class="text-lg font-bold text-gray-900">{{ $price->format() }}</span>
    @if($isDiscounted())
        <span class="text-sm line-through text-gray-400">{{ $oldPrice->format() }}</span>
        @if($showDiscount)
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-red-100 text-red-600">-{{ $discountPercentage() }}%</span>
        @endif
    @endif
</div>