@extends('layouts.riode')

@section('title', $product->name . ' - Riode Store')

@push('styles')
    <style>
        .single-product-page .product-single {
            margin-bottom: 3rem;
        }

        .single-product-page .product-gallery .product-image {
            background: #f7f7f7;
        }

        .single-product-page .product-gallery .product-image img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }

        .single-product-page .product-form-group {
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .single-product-page .btn-product {
            border: 0;
            cursor: pointer;
        }

        .single-product-page .product-details {
            padding-left: 2rem;
        }

        .single-product-page .product-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .8rem 2rem;
        }

        .single-product-page .product-price {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .single-product-page .product-stock-list {
            display: grid;
            gap: .7rem;
            margin: 0 0 2rem;
            padding: 0;
            list-style: none;
        }

        .single-product-page .product-stock-list li {
            display: flex;
            align-items: baseline;
            gap: .6rem;
            color: #666;
        }

        .single-product-page .product-stock-list strong {
            color: #222;
        }

        .single-product-page .product-options {
            display: flex;
            flex-wrap: wrap;
            gap: 1.2rem 1.6rem;
            margin-bottom: 2rem;
        }

        .single-product-page .product-options .product-form {
            min-width: 19rem;
            margin-bottom: 0;
        }

        .single-product-page .product-options label {
            display: block;
            margin-bottom: .6rem;
        }

        .single-product-page .btn-cart-submit {
            min-width: 18rem;
            min-height: 4.6rem;
            padding: 1.1rem 2.4rem;
            background: #26c;
            color: #fff;
            border-radius: .3rem;
            font-weight: 700;
            line-height: 1;
        }

        .single-product-page .btn-cart-submit:hover {
            background: #222;
            color: #fff;
        }

        .single-product-page .btn-cart-submit:disabled {
            background: #e1e5e8;
            color: #999;
            cursor: not-allowed;
        }

        .single-product-page .quantity {
            text-align: center;
        }

        .single-product-page .product-single .social-links {
            align-items: center;
        }

        @media (max-width: 767px) {
            .single-product-page .product-details {
                padding-left: 0;
                padding-top: 2.5rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $imageUrl = function ($path, $directory = 'uploads/products') {
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

            return asset(trim($directory, '/') . '/' . $path);
        };

        $mainImage = trim((string) ($product->image ?: 'product1.jpg'));
        $galleryImages = collect([$mainImage])
            ->merge(collect(explode(',', (string) $product->images))->map(fn ($image) => trim($image)))
            ->filter()
            ->unique()
            ->values();

        $regularPrice = (float) ($product->regular_price ?: 0);
        $salePrice = $product->sale_price ? (float) $product->sale_price : null;
        $cartPrice = $salePrice ?: $regularPrice;
        $discount = $salePrice && $regularPrice > 0
            ? max(1, round((($regularPrice - $salePrice) / $regularPrice) * 100))
            : null;

        try {
            $wishlistContent = \Surfsidemedia\Shoppingcart\Facades\Cart::instance('wishlist')->content();
            $wishlistItem = $wishlistContent->where('id', $product->id)->first();
        } catch (\Throwable $exception) {
            $wishlistItem = null;
        }

        $stockQuantity = (int) ($product->quntity ?? 0);
        $stockText = $product->stock_status === 'instock' ? 'In Stock' : 'Out of Stock';
        $variantColors = ['White', 'Black', 'Brown', 'Red', 'Green', 'Yellow'];
        $variantSizes = ['Small', 'Medium', 'Large', 'Extra Large'];
        $selectedColor = old('color', $variantColors[0]);
        $selectedSize = old('size', $variantSizes[1] ?? $variantSizes[0]);
        $canAddToCart = $product->stock_status !== 'outofstock' && $stockQuantity > 0;
    @endphp

    <main class="main single-product-page">
        <nav class="breadcrumb-nav">
            <div class="container">
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}"><i class="d-icon-home"></i></a></li>
                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                    @if ($product->category)
                        <li><a href="{{ route('shop.index', ['categories' => $product->category_id]) }}">{{ $product->category->name }}</a></li>
                    @endif
                    <li>{{ $product->name }}</li>
                </ul>
            </div>
        </nav>

        <div class="page-content mb-10 pb-6">
            <div class="container">
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

                @if ($errors->any())
                    <div class="alert alert-danger alert-simple alert-inline mb-4">
                        <h4 class="alert-title">Error:</h4> {{ $errors->first() }}
                    </div>
                @endif

                <div class="product product-single row">
                    <div class="col-md-6">
                        <div class="product-gallery pg-vertical">
                            <div class="product-single-carousel owl-carousel owl-theme owl-nav-inner row cols-1">
                                @foreach ($galleryImages as $image)
                                    <figure class="product-image">
                                        <img src="{{ $imageUrl($image) }}"
                                            data-zoom-image="{{ $imageUrl($image) }}"
                                            alt="{{ $product->name }}" width="800" height="900">
                                    </figure>
                                @endforeach
                            </div>
                            <div class="product-thumbs-wrap">
                                <div class="product-thumbs">
                                    @foreach ($galleryImages as $image)
                                        <div class="product-thumb {{ $loop->first ? 'active' : '' }}">
                                            <img src="{{ $imageUrl($image, 'uploads/products/thumbnails') }}"
                                                alt="{{ $product->name }}" width="109" height="122">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="thumb-up disabled" title="Previous"><i class="fas fa-chevron-left"></i></button>
                                <button class="thumb-down disabled" title="Next"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="product-details">
                            <h1 class="product-name">{{ $product->name }}</h1>

                            <div class="product-meta">
                                SKU: <span class="product-sku">{{ $product->SKU ?: 'N/A' }}</span>
                                @if ($product->category)
                                    <span class="product-brand ml-2">Category: <a href="{{ route('shop.index', ['categories' => $product->category_id]) }}">{{ $product->category->name }}</a></span>
                                @endif
                            </div>

                            <div class="product-price">
                                @if ($salePrice)
                                    <ins class="new-price">${{ number_format($salePrice, 2) }}</ins>
                                    <del class="old-price">${{ number_format($regularPrice, 2) }}</del>
                                    @if ($discount)
                                        <span class="product-label label-sale ml-2">{{ $discount }}% off</span>
                                    @endif
                                @else
                                    <span class="price">${{ number_format($regularPrice, 2) }}</span>
                                @endif
                            </div>

                            <div class="ratings-container">
                                <div class="ratings-full">
                                    <span class="ratings" style="width:{{ $product->featured ? 90 : 80 }}%"></span>
                                    <span class="tooltiptext tooltip-top"></span>
                                </div>
                                <a href="#product-tab-reviews" class="rating-reviews">( {{ $product->featured ? 12 : 8 }} reviews )</a>
                            </div>

                            <p class="product-short-desc">{{ $product->short_description }}</p>

                            <ul class="product-stock-list">
                                <li><span>Availability:</span><strong>{{ $stockText }}</strong></li>
                                @if ($stockQuantity > 0)
                                    <li><span>Stock:</span><strong>Only {{ $stockQuantity }} left</strong></li>
                                @endif
                                @if ($product->brand)
                                    <li><span>Brand:</span><strong>{{ $product->brand->name }}</strong></li>
                                @endif
                            </ul>

                            <form action="{{ route('cart.add') }}" method="POST" class="product-form product-qty js-backend-form">
                                @csrf
                                <input type="hidden" name="id" value="{{ $product->id }}">
                                <input type="hidden" name="name" value="{{ $product->name }}">
                                <input type="hidden" name="price" value="{{ $cartPrice }}">
                                <input type="hidden" name="has_variants" value="1">

                                <div class="product-options">
                                    <div class="product-form product-color">
                                        <label for="product-color">Color:</label>
                                        <div class="select-box">
                                            <select name="color" id="product-color" class="form-control" required>
                                                @foreach ($variantColors as $color)
                                                    <option value="{{ $color }}" @selected($selectedColor === $color)>{{ $color }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('color')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="product-form product-size">
                                        <label for="product-size">Size:</label>
                                        <div class="select-box">
                                            <select name="size" id="product-size" class="form-control" required>
                                                @foreach ($variantSizes as $size)
                                                    <option value="{{ $size }}" @selected($selectedSize === $size)>{{ $size }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('size')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <div class="product-form-group">
                                    <div class="input-group mr-2">
                                        <button class="quantity-minus d-icon-minus" type="button" title="Decrease quantity" @disabled(! $canAddToCart)></button>
                                        <input class="quantity form-control" name="quantity" type="number" min="1" max="{{ $stockQuantity ?: 1 }}" value="1" title="Quantity" @disabled(! $canAddToCart)>
                                        <button class="quantity-plus d-icon-plus" type="button" title="Increase quantity" @disabled(! $canAddToCart)></button>
                                    </div>
                                    <button type="submit" class="btn-product btn-cart-submit text-normal ls-normal" @disabled(! $canAddToCart)>
                                        <i class="d-icon-bag"></i>{{ $canAddToCart ? 'Add to Cart' : 'Out of Stock' }}
                                    </button>
                                </div>
                            </form>

                            <hr class="product-divider">

                            <div class="product-footer">
                                <div class="social-links mr-4">
                                    <span class="mr-2">Share:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->fullUrl()) }}" class="social-link social-facebook fab fa-facebook-f" target="_blank" rel="noopener" title="Facebook"></a>
                                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->fullUrl()) }}&text={{ urlencode($product->name) }}" class="social-link social-twitter fab fa-twitter" target="_blank" rel="noopener" title="Twitter"></a>
                                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(request()->fullUrl()) }}" class="social-link social-linkedin fab fa-linkedin-in" target="_blank" rel="noopener" title="LinkedIn"></a>
                                </div>

                                @if ($wishlistItem)
                                    <form action="{{ route('wishlist.item.remove', ['rowId' => $wishlistItem->rowId]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-product btn-wishlist text-normal ls-normal">
                                            <i class="d-icon-heart-full"></i>Remove from Wishlist
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('wishlist.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $product->id }}">
                                        <input type="hidden" name="name" value="{{ $product->name }}">
                                        <input type="hidden" name="price" value="{{ $cartPrice }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn-product btn-wishlist text-normal ls-normal">
                                            <i class="d-icon-heart"></i>Add to Wishlist
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab tab-nav-simple product-tabs">
                    <ul class="nav nav-tabs justify-content-center" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" href="#product-tab-description" data-toggle="tab">Description</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#product-tab-additional" data-toggle="tab">Additional Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#product-tab-reviews" data-toggle="tab">Reviews</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active in" id="product-tab-description">
                            <p>{{ $product->description ?: $product->short_description }}</p>
                        </div>
                        <div class="tab-pane" id="product-tab-additional">
                            <ul class="list-none">
                                <li><label>SKU:</label> {{ $product->SKU ?: 'N/A' }}</li>
                                <li><label>Category:</label> {{ optional($product->category)->name ?: 'N/A' }}</li>
                                <li><label>Brand:</label> {{ optional($product->brand)->name ?: 'N/A' }}</li>
                                <li><label>Stock:</label> {{ $stockText }}</li>
                            </ul>
                        </div>
                        <div class="tab-pane" id="product-tab-reviews">
                            <p>Customer reviews will appear here after review collection is enabled.</p>
                        </div>
                    </div>
                </div>

                @if ($rproducts->isNotEmpty())
                    <section class="mt-10 pt-6">
                        <h2 class="title title-center">Related Products</h2>
                        <div class="owl-carousel owl-theme row cols-2 cols-md-3 cols-lg-4" data-owl-options='{
                            "items": 4,
                            "margin": 20,
                            "nav": true,
                            "dots": false,
                            "responsive": {
                                "0": { "items": 2 },
                                "768": { "items": 3 },
                                "992": { "items": 4 }
                            }
                        }'>
                            @foreach ($rproducts as $relatedProduct)
                                @include('partials.riode-product-card', ['product' => $relatedProduct])
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </main>
@endsection
