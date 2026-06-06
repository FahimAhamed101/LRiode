@extends('layouts.app')

@section('content')
    @php
        $wishlistItems = collect($items ?? []);
    @endphp

    <main class="pt-90">
        <div class="mb-4 pb-4"></div>
        <section class="my-account container">
            <h2 class="page-title">Wishlist</h2>

            <div class="row">
                <div class="col-lg-3">
                    @include('user.account-nav')
                </div>

                <div class="col-lg-9">
                    <div class="page-content my-account__wishlist">
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

                        @if ($wishlistItems->isNotEmpty())
                            <div class="shopping-cart">
                                <div class="cart-table__wrapper">
                                    <table class="cart-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th></th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Action</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($wishlistItems as $item)
                                                @php
                                                    $product = $item->model;
                                                    $image = optional($product)->image ?: 'product1.jpg';
                                                    $productUrl = $product
                                                        ? route('shop.product.details', $product->slug)
                                                        : route('shop.index');
                                                    $imageUrl = \Illuminate\Support\Str::startsWith($image, ['http://', 'https://', 'riode/', 'uploads/'])
                                                        ? asset($image)
                                                        : asset('uploads/products/thumbnails/' . $image);
                                                    $itemOptions = collect($item->options)->filter(fn ($value) => filled($value));
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="shopping-cart__product-item">
                                                            <a href="{{ $productUrl }}">
                                                                <img loading="lazy" src="{{ $imageUrl }}" width="120" height="120" alt="{{ $item->name }}" />
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="shopping-cart__product-item__detail">
                                                            <h4>
                                                                <a href="{{ $productUrl }}">
                                                                    {{ $item->name }}
                                                                </a>
                                                            </h4>
                                                            @if ($itemOptions->isNotEmpty())
                                                                <ul class="shopping-cart__product-item__options">
                                                                    @foreach ($itemOptions as $optionName => $optionValue)
                                                                        <li>{{ \Illuminate\Support\Str::headline($optionName) }}: {{ $optionValue }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="shopping-cart__product-price">${{ number_format((float) $item->price, 2) }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $item->qty }}
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-2">
                                                            <form method="POST" action="{{ route('wishlist.to.cart', ['rowId' => $item->rowId]) }}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-warning">Move to Cart</button>
                                                            </form>

                                                            <form action="{{ route('wishlist.item.remove', ['rowId' => $item->rowId]) }}" method="POST" id="remove-item-{{ $item->rowId }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="cart-table-footer mt-4">
                                    <form action="{{ route('wishlist.empty') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-light" type="submit">Clear Wishlist</button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-md-12">
                                    <p>No item found in your wishlist.</p>
                                    <a href="{{ route('shop.index') }}" class="btn btn-info">Continue Shopping</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
