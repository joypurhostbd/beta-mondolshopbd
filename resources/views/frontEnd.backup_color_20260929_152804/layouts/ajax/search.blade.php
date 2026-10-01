@if($products && count($products) > 0)
<div class="search_product">
    <ul>
        @foreach($products as $value)
        <li>
            <a href="{{ route('product', $value->slug) }}" class="search_item_link" style="display: grid; grid-template-columns: 65px auto; grid-gap: 15px; width: 100%; color: inherit; text-decoration: none; align-items: center;">
                <div class="search_img">
                    <img src="{{ asset(($value->featuredImage ?? $value->image)?->image ?? '') }}" alt="{{ $value->name }}" loading="lazy">
                </div>
                <div class="search_content">
                    <p class="name">{{ $value->name }}</p>                 
                    <p class="price">৳{{ $value->new_price }} @if($value->old_price)<del>৳{{ $value->old_price }}</del>@endif</p>
                </div>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif