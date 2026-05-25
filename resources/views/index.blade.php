@extends('layouts.riode')

@section('title', 'Riode Store')

@push('styles')
    <style>
        .product-action-form {
            display: inline-block;
            margin: 0;
        }

        .product-action-form button {
            border: 0;
            cursor: pointer;
        }

        .intro-slide1 .banner-content,
        .intro-slide2 .banner-content {
            z-index: 2;
        }

        .riode-empty-state {
            padding: 4rem 1rem;
            text-align: center;
            color: #666;
        }
    </style>
@endpush

@section('content')
    @php
        $assetFrom = function (?string $path, string $directory, string $fallback) {
            $path = $path ?: $fallback;

            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
                return $path;
            }

            if (\Illuminate\Support\Str::startsWith($path, ['riode/', 'uploads/'])) {
                return asset($path);
            }

            return asset(trim($directory, '/') . '/' . $path);
        };

        $productImage = function ($product) use ($assetFrom) {
            return $assetFrom($product->image ?? null, 'uploads/products', 'product1.jpg');
        };

        $slidesForView = collect($slides)->isNotEmpty()
            ? collect($slides)
            : collect([
                (object) [
                    'title' => 'Fashionable',
                    'subtitle' => 'Collection',
                    'tagline' => 'Buy 2 Get 1 Free',
                    'link' => route('shop.index'),
                    'image' => 'slide1.jpg',
                ],
                (object) [
                    'title' => 'Riode Birthday',
                    'subtitle' => 'Sale',
                    'tagline' => 'Up to 70% off',
                    'link' => route('shop.index'),
                    'image' => 'slide2.jpg',
                ],
            ]);

        $categoryCards = collect($categories)->take(4);
        $bestSellers = collect($sproducts)->take(5);
        $featuredProducts = collect($fproducts)->take(5);
        $widgetProducts = collect($sproducts)->merge($fproducts)->unique('id')->values();
    @endphp

    <main class="main">
        <div class="page-content">
            <section class="intro-section">
                <div class="owl-carousel owl-theme row owl-dot-inner owl-dot-white intro-slider animation-slider cols-1 gutter-no"
                    data-owl-options='{
                        "nav": false,
                        "dots": true,
                        "loop": false,
                        "items": 1,
                        "autoplay": true,
                        "autoplayTimeout": 8000
                    }'>
                    @foreach ($slidesForView as $index => $slide)
                        <div class="banner banner-fixed intro-slide{{ $index + 1 }}" style="background-color: {{ $index === 0 ? '#46b2e8' : '#dddee0' }};">
                            <figure>
                                <img src="{{ $assetFrom($slide->image, 'uploads/slides', $index === 0 ? 'slide1.jpg' : 'slide2.jpg') }}"
                                    alt="{{ $slide->title }}" width="1903" height="630" style="background-color: {{ $index === 0 ? '#34ace5' : '#d8d9d9' }};">
                            </figure>
                            <div class="container">
                                <div class="banner-content y-50 {{ $index === 1 ? 'ml-auto text-right' : '' }}">
                                    <h4 class="banner-subtitle font-weight-bold ls-l">
                                        <span class="d-inline-block">{{ $slide->tagline }}</span>
                                    </h4>
                                    <h2 class="banner-title font-weight-bold {{ $index === 0 ? 'text-white' : 'text-primary' }} lh-1 ls-md">
                                        {{ $slide->title }}
                                    </h2>
                                    <h3 class="font-weight-normal lh-1 ls-l {{ $index === 0 ? 'text-white' : 'text-dark' }}">
                                        {{ $slide->subtitle }}
                                    </h3>
                                    <p class="{{ $index === 0 ? 'text-white' : 'text-dark' }} ls-s mb-7">
                                        Get free shipping on all orders over $99.00
                                    </p>
                                    <a href="{{ $slide->link ?: route('shop.index') }}" class="btn btn-dark btn-rounded">
                                        Shop Now<i class="d-icon-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="container mt-6 appear-animate">
                    <div class="service-list">
                        <div class="owl-carousel owl-theme row cols-lg-3 cols-sm-2 cols-1" data-owl-options='{
                            "items": 3,
                            "nav": false,
                            "dots": false,
                            "loop": true,
                            "autoplay": false,
                            "responsive": {
                                "0": {"items": 1},
                                "576": {"items": 2},
                                "768": {"items": 3, "loop": false}
                            }
                        }'>
                            <div class="icon-box icon-box-side icon-box1">
                                <i class="icon-box-icon d-icon-truck"></i>
                                <div class="icon-box-content">
                                    <h4 class="icon-box-title text-capitalize ls-normal lh-1">Free Shipping &amp; Return</h4>
                                    <p class="ls-s lh-1">Free shipping on orders over $99</p>
                                </div>
                            </div>
                            <div class="icon-box icon-box-side icon-box2">
                                <i class="icon-box-icon d-icon-service"></i>
                                <div class="icon-box-content">
                                    <h4 class="icon-box-title text-capitalize ls-normal lh-1">Customer Support 24/7</h4>
                                    <p class="ls-s lh-1">Instant access to perfect support</p>
                                </div>
                            </div>
                            <div class="icon-box icon-box-side icon-box3">
                                <i class="icon-box-icon d-icon-secure"></i>
                                <div class="icon-box-content">
                                    <h4 class="icon-box-title text-capitalize ls-normal lh-1">100% Secure Payment</h4>
                                    <p class="ls-s lh-1">We ensure secure payment.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="pt-10 mt-7 appear-animate">
                <div class="container">
                    <h2 class="title title-center mb-5">Our Categories</h2>
                    <div class="row">
                        @forelse ($categoryCards as $index => $category)
                            <div class="col-xs-6 col-lg-3 mb-4">
                                <div class="category category-default1 category-absolute banner-radius overlay-zoom">
                                    <a href="{{ route('shop.index', ['categories' => $category->id]) }}">
                                        <figure class="category-media">
                                            <img src="{{ $assetFrom($category->image, 'uploads/categories', 'category' . ($index + 1) . '.jpg') }}"
                                                alt="{{ $category->name }}" width="280" height="280" style="background-color: #ececef;">
                                        </figure>
                                    </a>
                                    <div class="category-content">
                                        <h4 class="category-name font-weight-bold ls-l">
                                            <a href="{{ route('shop.index', ['categories' => $category->id]) }}">{{ $category->name }}</a>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 riode-empty-state">Run the database seeder to load demo categories.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="product-wrapper container appear-animate mt-6 mt-md-10 pt-4 pb-8">
                <h2 class="title title-center mb-5">Best Sellers</h2>
                @if ($bestSellers->isNotEmpty())
                    <div class="owl-carousel owl-theme row owl-nav-full cols-2 cols-md-3 cols-lg-4" data-owl-options='{
                        "items": 5,
                        "nav": false,
                        "loop": false,
                        "dots": true,
                        "margin": 20,
                        "responsive": {
                            "0": {"items": 2},
                            "768": {"items": 3},
                            "992": {"items": 4, "dots": false, "nav": true}
                        }
                    }'>
                        @foreach ($bestSellers as $product)
                            @include('partials.riode-product-card', ['product' => $product])
                        @endforeach
                    </div>
                @else
                    <div class="riode-empty-state">Run the database seeder to load demo products.</div>
                @endif
            </section>

            <section class="banner-group mt-4">
                <div class="container">
                    <h2 class="title d-none">Banner Group</h2>
                    <div class="row justify-content-center">
                        <div class="col-lg-4 col-sm-6 mb-4">
                            <div class="banner banner-3 overlay-zoom banner-fixed banner-radius content-middle appear-animate">
                                <figure>
                                    <img src="{{ asset('riode/images/demos/demo1/banners/banner1.jpg') }}" alt="Men banner" width="380" height="207" style="background-color: #836648;">
                                </figure>
                                <div class="banner-content">
                                    <h3 class="banner-title text-white mb-1">For Men's</h3>
                                    <h4 class="banner-subtitle text-uppercase font-weight-normal text-white">Starting at $29</h4>
                                    <hr class="banner-divider">
                                    <a href="{{ route('shop.index') }}" class="btn btn-white btn-link btn-underline">Shop Now<i class="d-icon-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 mb-4 order-lg-auto order-sm-last">
                            <div class="banner banner-4 banner-fixed banner-radius overlay-effect2 content-middle content-center appear-animate">
                                <figure>
                                    <img src="{{ asset('riode/images/demos/demo1/banners/banner2.jpg') }}" alt="Sale banner" width="350" height="177" style="background-color: #1e1e1e;">
                                </figure>
                                <div class="banner-content d-flex align-items-center w-100 text-left">
                                    <div class="mr-auto mb-4 mb-md-0">
                                        <h4 class="banner-subtitle text-white">Up to 20% Off<br><span class="ls-l">Black Friday</span></h4>
                                        <h3 class="banner-title text-primary font-weight-bold lh-1 mb-0">Sale</h3>
                                    </div>
                                    <a href="{{ route('shop.index') }}" class="btn btn-primary btn-outline btn-rounded font-weight-bold text-white">Shop Now<i class="d-icon-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 mb-4">
                            <div class="banner overlay-zoom banner-5 banner-fixed banner-radius content-middle appear-animate">
                                <figure>
                                    <img src="{{ asset('riode/images/demos/demo1/banners/banner3.jpg') }}" alt="Fashion banner" width="380" height="207" style="background-color: #97928b;">
                                </figure>
                                <div class="banner-content">
                                    <h3 class="banner-title text-white mb-1">Fashions</h3>
                                    <h4 class="banner-subtitle text-uppercase font-weight-normal text-white">30% Off</h4>
                                    <hr class="banner-divider">
                                    <a href="{{ route('shop.index') }}" class="btn btn-white btn-link btn-underline">Shop Now<i class="d-icon-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="product-wrapper mt-6 mt-md-10 pt-4 mb-10 pb-2 container appear-animate">
                <h2 class="title title-center">Our Featured</h2>
                @if ($featuredProducts->isNotEmpty())
                    <div class="owl-carousel owl-theme row cols-2 cols-md-3 cols-lg-4 cols-xl-5" data-owl-options='{
                        "items": 5,
                        "nav": false,
                        "loop": false,
                        "dots": true,
                        "margin": 20,
                        "responsive": {
                            "0": {"items": 2},
                            "768": {"items": 3},
                            "992": {"items": 4},
                            "1200": {"items": 5, "dots": false, "nav": true}
                        }
                    }'>
                        @foreach ($featuredProducts as $product)
                            @include('partials.riode-product-card', ['product' => $product])
                        @endforeach
                    </div>
                @else
                    <div class="riode-empty-state">Run the database seeder to load featured products.</div>
                @endif
            </section>

            <section class="banner banner-background parallax text-center" data-option='{"offset": -60}'
                data-image-src="{{ asset('riode/images/demos/demo1/parallax.jpg') }}" style="background-color: #2d2f33;">
                <div class="container">
                    <div class="banner-content appear-animate">
                        <h4 class="banner-subtitle text-white font-weight-bold ls-l">
                            Extra<span class="d-inline-block label-star bg-dark text-primary ml-4 mr-2">30% Off</span>Online
                        </h4>
                        <h3 class="banner-title font-weight-bold text-white">Summer Season Sale</h3>
                        <p class="text-white ls-s">Free shipping on orders over $99</p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary btn-rounded btn-icon-right">Shop Now<i class="d-icon-arrow-right"></i></a>
                    </div>
                </div>
            </section>

            <section class="mt-2 pb-6 pt-10 pb-md-10 appear-animate">
                <h2 class="title d-none">Our Brand</h2>
                <div class="container">
                    <div class="owl-carousel owl-theme row brand-carousel cols-xl-6 cols-lg-5 cols-md-4 cols-sm-3 cols-2"
                        data-owl-options='{
                            "nav": false,
                            "dots": false,
                            "autoplay": true,
                            "margin": 20,
                            "loop": true,
                            "responsive": {
                                "0": {"items": 2},
                                "576": {"items": 3},
                                "768": {"items": 4},
                                "992": {"items": 5},
                                "1200": {"items": 6}
                            }
                        }'>
                        @for ($i = 1; $i <= 6; $i++)
                            <figure><img src="{{ asset('riode/images/brands/' . $i . '.png') }}" alt="Brand {{ $i }}" width="180" height="100"></figure>
                        @endfor
                    </div>
                </div>
            </section>

            <section class="product-widget-wrapper pb-2 pb-md-10 appear-animate">
                <div class="container">
                    <div class="row">
                        @foreach (['Sale Products', 'Latest Products', 'Best of the Week', 'Popular'] as $sectionIndex => $title)
                            <div class="col-lg-3 col-sm-6 mb-4">
                                <div class="widget widget-products">
                                    <h4 class="widget-title border-no lh-1 font-weight-bold">{{ $title }}</h4>
                                    <div class="products-col">
                                        @foreach ($widgetProducts->slice($sectionIndex * 3, 3) as $product)
                                            <div class="product product-list-sm">
                                                <figure class="product-media">
                                                    <a href="{{ route('shop.product.details', $product->slug) }}">
                                                        <img src="{{ $productImage($product) }}" alt="{{ $product->name }}" width="100" height="114" style="background-color: #f2f3f5;">
                                                    </a>
                                                </figure>
                                                <div class="product-details">
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
                                                            <span class="ratings" style="width:60%"></span>
                                                            <span class="tooltiptext tooltip-top"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection
