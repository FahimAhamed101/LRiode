@extends('layouts.riode')

@section('title', 'Shopping Cart - Riode Store')

@push('styles')
    <style>
        .cart-page .step-by {
            display: flex;
            justify-content: center;
            gap: 4rem;
            border-bottom: 1px solid #e1e1e1;
            margin-bottom: 4rem;
            padding-bottom: 2rem;
        }

        .cart-page .title-step {
            margin-bottom: 0;
            font-size: 1.8rem;
            color: #999;
        }

        .cart-page .title-step.active {
            color: #222;
        }

        .cart-page .cart-table .product-thumbnail img {
            width: 9rem;
            height: 9rem;
            object-fit: cover;
            background: #f7f7f7;
        }

        .cart-page .cart-table .product-name-section a {
            display: inline-block;
            margin-bottom: .5rem;
            color: #222;
            font-weight: 600;
        }

        .cart-page .cart-item-options {
            margin: 0;
            padding: 0;
            list-style: none;
            color: #999;
            font-size: 1.3rem;
        }

        .cart-page .cart-quantity-control {
            display: inline-flex;
            align-items: stretch;
            border: 1px solid #eee;
            border-radius: .3rem;
            overflow: hidden;
        }

        .cart-page .cart-quantity-control form {
            margin: 0;
        }

        .cart-page .cart-quantity-control button,
        .cart-page .cart-quantity-control input {
            width: 4rem;
            height: 4rem;
            border: 0;
            background: #fff;
            text-align: center;
        }

        .cart-page .cart-quantity-control input {
            border-left: 1px solid #eee;
            border-right: 1px solid #eee;
            color: #222;
            font-weight: 600;
        }

        .cart-page .product-remove {
            border: 0;
            background: transparent;
            color: #999;
            cursor: pointer;
        }

        .cart-page .cart-coupon-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .cart-page .cart-coupon-form .form-control {
            max-width: 34rem;
            margin-bottom: 0;
        }

        .cart-page .summary {
            border: 1px solid #e1e1e1;
            padding: 2.5rem 3rem 3rem;
        }

        .cart-page .summary table {
            width: 100%;
        }

        .cart-page .summary td {
            padding: 1.2rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-page .summary td:last-child {
            text-align: right;
        }

        .cart-page .summary-total-price {
            color: #222;
            font-size: 2rem;
            font-weight: 700;
        }

        .cart-page .cart-empty {
            padding: 6rem 1rem 7rem;
            text-align: center;
        }

        @media (max-width: 767px) {
            .cart-page .step-by {
                align-items: flex-start;
                flex-direction: column;
                gap: 1rem;
            }

            .cart-page .cart-coupon-form .form-control {
                max-width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $cart = \Surfsidemedia\Shoppingcart\Facades\Cart::instance('cart');
        $imageUrl = function ($item) {
            $product = $item->model;
            $image = trim((string) optional($product)->image);

            if ($image === '') {
                $image = 'product1.jpg';
            }

            if (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])) {
                return $image;
            }

            if (\Illuminate\Support\Str::startsWith($image, ['riode/', 'uploads/', 'assets/'])) {
                return asset($image);
            }

            return asset('uploads/products/thumbnails/' . $image);
        };

        $productUrl = function ($item) {
            $product = $item->model;

            return $product && $product->slug
                ? route('shop.product.details', $product->slug)
                : route('cart.index');
        };

        $hasDiscount = session()->has('discounts');
        $subtotal = $hasDiscount ? session('discounts.subtotal') : $cart->subtotal();
        $tax = $hasDiscount ? session('discounts.tax') : $cart->tax();
        $total = $hasDiscount ? session('discounts.total') : $cart->total();
    @endphp

    <main class="main cart cart-page">
        <div class="page-content pt-7 pb-10">
            <div class="step-by pr-4 pl-4">
                <h3 class="title title-simple title-step active"><a href="{{ route('cart.index') }}">1. Shopping Cart</a></h3>
                <h3 class="title title-simple title-step"><a href="{{ route('cart.checkout') }}">2. Checkout</a></h3>
                <h3 class="title title-simple title-step"><a href="{{ route('cart.order.confirmation') }}">3. Order Complete</a></h3>
            </div>

            <div class="container mt-7 mb-2">
                @if (session('success'))
                    <div class="alert alert-success alert-simple alert-inline mb-4">
                        <h4 class="alert-title">Success:</h4> {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-simple alert-inline mb-4">
                        <h4 class="alert-title">Error:</h4> {{ session('error') }}
                    </div>
                @endif

                @if ($items->count() > 0)
                    <div class="row">
                        <div class="col-lg-8 col-md-12 pr-lg-4">
                            <table class="shop-table cart-table">
                                <thead>
                                    <tr>
                                        <th><span>Product</span></th>
                                        <th></th>
                                        <th><span>Price</span></th>
                                        <th><span>Quantity</span></th>
                                        <th><span>Subtotal</span></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        @php
                                            $itemOptions = collect($item->options)->filter(fn ($value) => filled($value));
                                        @endphp
                                        <tr>
                                            <td class="product-thumbnail">
                                                <figure>
                                                    <a href="{{ $productUrl($item) }}">
                                                        <img src="{{ $imageUrl($item) }}" width="100" height="100" alt="{{ $item->name }}">
                                                    </a>
                                                </figure>
                                            </td>
                                            <td class="product-name">
                                                <div class="product-name-section">
                                                    <a href="{{ $productUrl($item) }}">{{ $item->name }}</a>
                                                    @if ($itemOptions->isNotEmpty())
                                                        <ul class="cart-item-options">
                                                            @foreach ($itemOptions as $optionName => $optionValue)
                                                                <li>{{ \Illuminate\Support\Str::headline($optionName) }}: {{ $optionValue }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="product-subtotal">
                                                <span class="amount">${{ number_format((float) $item->price, 2) }}</span>
                                            </td>
                                            <td class="product-quantity">
                                                <div class="cart-quantity-control">
                                                    <form action="{{ route('cart.qty.decrease', ['rowId' => $item->rowId]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="quantity-minus d-icon-minus" type="submit" title="Decrease quantity"></button>
                                                    </form>
                                                    <input type="text" value="{{ $item->qty }}" readonly aria-label="Quantity">
                                                    <form action="{{ route('cart.qty.increase', ['rowId' => $item->rowId]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="quantity-plus d-icon-plus" type="submit" title="Increase quantity"></button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td class="product-price">
                                                <span class="amount">${{ $item->subtotal() }}</span>
                                            </td>
                                            <td class="product-close">
                                                <form method="POST" action="{{ route('cart.item.remove', ['rowId' => $item->rowId]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="product-remove" title="Remove {{ $item->name }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="cart-actions mb-6 pt-4">
                                <a href="{{ route('shop.index') }}" class="btn btn-dark btn-md btn-rounded btn-icon-left mr-4 mb-4">
                                    <i class="d-icon-arrow-left"></i>Continue Shopping
                                </a>
                                <form action="{{ route('cart.empty') }}" method="POST" class="d-inline-block mb-4">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline btn-dark btn-md btn-rounded">Clear Cart</button>
                                </form>
                            </div>

                            <div class="cart-coupon-box mb-8">
                                <h4 class="title coupon-title text-uppercase ls-m">Coupon Discount</h4>
                                @if (!session()->has('coupon'))
                                    <form action="{{ route('cart.coupon.apply') }}" method="POST" class="cart-coupon-form">
                                        @csrf
                                        <input type="text" name="coupon_code" class="input-text form-control text-grey ls-m" value="" placeholder="Enter coupon code here...">
                                        <button type="submit" class="btn btn-md btn-dark btn-rounded btn-outline">Apply Coupon</button>
                                    </form>
                                @else
                                    <form action="{{ route('cart.coupon.remove') }}" method="POST" class="cart-coupon-form">
                                        @csrf
                                        @method('DELETE')
                                        <input type="text" class="input-text form-control text-grey ls-m" value="{{ session('coupon.code') }} applied" readonly>
                                        <button type="submit" class="btn btn-md btn-dark btn-rounded btn-outline">Remove Coupon</button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <aside class="col-lg-4 sticky-sidebar-wrapper">
                            <div class="sticky-sidebar" data-sticky-options="{'bottom': 20}">
                                <div class="summary mb-4">
                                    <h3 class="summary-title text-left">Cart Totals</h3>
                                    <table>
                                        <tbody>
                                            <tr>
                                                <td><h4 class="summary-subtitle">Subtotal</h4></td>
                                                <td><p class="summary-subtotal-price">${{ $cart->subtotal() }}</p></td>
                                            </tr>
                                            @if ($hasDiscount)
                                                <tr>
                                                    <td><h4 class="summary-subtitle">Discount {{ session('coupon.code') }}</h4></td>
                                                    <td><p>${{ number_format((float) session('discounts.discount'), 2) }}</p></td>
                                                </tr>
                                                <tr>
                                                    <td><h4 class="summary-subtitle">After Discount</h4></td>
                                                    <td><p>${{ number_format((float) $subtotal, 2) }}</p></td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td><h4 class="summary-subtitle">Shipping</h4></td>
                                                <td><p>Free</p></td>
                                            </tr>
                                            <tr>
                                                <td><h4 class="summary-subtitle">VAT</h4></td>
                                                <td><p>${{ is_numeric($tax) ? number_format((float) $tax, 2) : $tax }}</p></td>
                                            </tr>
                                            <tr>
                                                <td><h4 class="summary-subtitle">Total</h4></td>
                                                <td><p class="summary-total-price ls-s">${{ is_numeric($total) ? number_format((float) $total, 2) : $total }}</p></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <a href="{{ route('cart.checkout') }}" class="btn btn-dark btn-rounded btn-checkout">Proceed to Checkout</a>
                                </div>
                            </div>
                        </aside>
                    </div>
                @else
                    <div class="cart-empty">
                        <i class="d-icon-bag cart-icon"></i>
                        <p class="cart-descri">No item found in your cart.</p>
                        <a href="{{ route('shop.index') }}" class="btn btn-dark btn-rounded">Shop Now</a>
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
