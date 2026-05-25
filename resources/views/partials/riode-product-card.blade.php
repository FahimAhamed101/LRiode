@php
    $productImage = $product->image ?: 'product1.jpg';

    if (\Illuminate\Support\Str::startsWith($productImage, ['http://', 'https://'])) {
        $productImageUrl = $productImage;
    } elseif (\Illuminate\Support\Str::startsWith($productImage, ['riode/', 'uploads/'])) {
        $productImageUrl = asset($productImage);
    } else {
        $productImageUrl = asset('uploads/products/' . $productImage);
    }

    $price = $product->sale_price ?: $product->regular_price;
    $discount = $product->sale_price
        ? max(1, round((($product->regular_price - $product->sale_price) / max($product->regular_price, 1)) * 100))
        : null;
@endphp

<div class="product text-center">
    <figure class="product-media">
        <a href="{{ route('shop.product.details', $product->slug) }}">
            <img src="{{ $productImageUrl }}" alt="{{ $product->name }}" width="280" height="315" style="background-color: #f2f3f5;">
        </a>
        <div class="product-label-group">
            @if ($discount)
                <span class="product-label label-sale">{{ $discount }}% off</span>
            @elseif ($product->featured)
                <label class="product-label label-new">New</label>
            @endif
        </div>
        <div class="product-action-vertical">
            <form action="{{ route('cart.add') }}" method="POST" class="product-action-form">
                @csrf
                <input type="hidden" name="id" value="{{ $product->id }}">
                <input type="hidden" name="name" value="{{ $product->name }}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="price" value="{{ $price }}">
                <button type="submit" class="btn-product-icon btn-cart" title="Add to cart"><i class="d-icon-bag"></i></button>
            </form>
            <form action="{{ route('wishlist.add') }}" method="POST" class="product-action-form">
                @csrf
                <input type="hidden" name="id" value="{{ $product->id }}">
                <input type="hidden" name="name" value="{{ $product->name }}">
                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="price" value="{{ $price }}">
                <button type="submit" class="btn-product-icon btn-wishlist" title="Add to wishlist"><i class="d-icon-heart"></i></button>
            </form>
        </div>
        <div class="product-action">
            <a href="{{ route('shop.product.details', $product->slug) }}" class="btn-product btn-quickview" title="View Details">View Details</a>
        </div>
    </figure>
    <div class="product-details">
        <div class="product-cat">
            <a href="{{ route('shop.index', ['categories' => $product->category_id]) }}">{{ optional($product->category)->name ?? 'Fashion' }}</a>
        </div>
        <h3 class="product-name">
            <a href="{{ route('shop.product.details', $product->slug) }}">{{ $product->name }}</a>
        </h3>
        <div class="product-price">
            @if ($product->sale_price)
                <ins class="new-price">${{ number_format($product->sale_price, 2) }}</ins>
                <del class="old-price">${{ number_format($product->regular_price, 2) }}</del>
            @else
                <span class="price">${{ number_format($product->regular_price, 2) }}</span>
            @endif
        </div>
        <div class="ratings-container">
            <div class="ratings-full">
                <span class="ratings" style="width:{{ $product->featured ? 80 : 60 }}%"></span>
                <span class="tooltiptext tooltip-top"></span>
            </div>
            <a href="{{ route('shop.product.details', $product->slug) }}" class="rating-reviews">( {{ $product->featured ? 12 : 8 }} reviews )</a>
        </div>
    </div>
</div>
