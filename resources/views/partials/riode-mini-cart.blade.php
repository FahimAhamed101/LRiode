@php
    $items = collect($cartItems ?? []);
    $count = $cartCount ?? (int) $items->sum('qty');
    $subtotal = $cartSubtotal ?? '0.00';

    $miniCartImage = function (?string $path) {
        $path = trim((string) $path);

        if ($path === '') {
            $path = 'product1.jpg';
        }

        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['riode/', 'uploads/', 'assets/'])) {
            return asset($path);
        }

        return asset('uploads/products/thumbnails/' . $path);
    };
@endphp

<div class="dropdown cart-dropdown type2 off-canvas mr-0 mr-lg-2">
    <a href="{{ route('cart.index') }}" class="cart-toggle label-block link">
        <div class="cart-label d-lg-show">
            <span class="cart-name">Shopping Cart:</span>
            <span class="cart-price">${{ $subtotal }}</span>
        </div>
        <i class="d-icon-bag"><span class="cart-count">{{ $count }}</span></i>
    </a>
    <div class="canvas-overlay"></div>
    <div class="dropdown-box">
        <div class="canvas-header">
            <h4 class="canvas-title">Shopping Cart</h4>
            <a href="#" class="btn btn-dark btn-link btn-icon-right btn-close">
                close<i class="d-icon-arrow-right"></i><span class="sr-only">Cart</span>
            </a>
        </div>

        <div class="products scrollable">
            @forelse ($items as $item)
                @php
                    $cartProduct = $item->model;
                    $productUrl = $cartProduct && $cartProduct->slug
                        ? route('shop.product.details', $cartProduct->slug)
                        : route('cart.index');
                    $productImage = $miniCartImage($cartProduct ? $cartProduct->image : null);
                @endphp

                <div class="product product-cart">
                    <figure class="product-media">
                        <a href="{{ $productUrl }}">
                            <img src="{{ $productImage }}" alt="{{ $item->name }}" width="80" height="88">
                        </a>
                        <form method="POST" action="{{ route('cart.item.remove', ['rowId' => $item->rowId]) }}" class="mini-cart-remove-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link mini-cart-remove" title="Remove {{ $item->name }}">
                                <i class="fas fa-times"></i><span class="sr-only">Remove</span>
                            </button>
                        </form>
                    </figure>
                    <div class="product-detail">
                        <a href="{{ $productUrl }}" class="product-name">{{ $item->name }}</a>
                        <div class="price-box">
                            <span class="product-quantity">{{ $item->qty }}</span>
                            <span class="product-price">${{ number_format((float) $item->price, 2) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center mb-4">No item found in your cart.</p>
            @endforelse
        </div>

        <div class="cart-total">
            <label>Subtotal:</label>
            <span class="price">${{ $subtotal }}</span>
        </div>
        <div class="cart-action">
            <a href="{{ route('cart.index') }}" class="btn btn-dark btn-link">View Cart</a>
            <a href="{{ route('cart.checkout') }}" class="btn btn-dark"><span>Go To Checkout</span></a>
        </div>
    </div>
</div>
