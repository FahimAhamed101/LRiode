@extends('layouts.riode')

@section('title', 'Shop - Riode Store')
@section('body_class', 'shop')

@push('styles')
    <style>
        .shop-demo-page .shop-banner {
            min-height: 24rem;
            margin-bottom: 2.5rem;
            background-position: center;
            background-size: cover;
        }

        .shop-demo-page .shop-banner .banner-content {
            max-width: 38rem;
            padding: 4rem 3rem;
        }

        .shop-demo-page .shop-banner .banner-title {
            font-size: 3.2rem;
            line-height: 1.15;
        }

        .shop-demo-page .shop-banner .banner-title > strong {
            color: #26c;
            font-size: 4.2rem;
            font-weight: 800;
        }

        .shop-demo-page .filter-items li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: .9rem;
        }

        .shop-demo-page .filter-link {
            display: inline-flex;
            align-items: center;
            gap: .8rem;
            min-width: 0;
            color: #666;
        }

        .shop-demo-page .filter-link input {
            width: 1.6rem;
            height: 1.6rem;
            border: 1px solid #ccc;
            -webkit-appearance: auto;
            appearance: auto;
            flex: 0 0 auto;
        }

        .shop-demo-page .filter-count {
            color: #aaa;
            font-size: 1.2rem;
        }

        .shop-demo-page .price-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .shop-demo-page .price-inputs .form-control {
            min-height: 4.2rem;
        }

        .shop-demo-page .product-wrapper .product-wrap {
            margin-bottom: 3rem;
        }

        .shop-demo-page .product-media {
            background: #f7f8fa;
        }

        .shop-demo-page .product-media img {
            aspect-ratio: 1 / 1;
            object-fit: contain;
            width: 100%;
        }

        .shop-demo-page .empty-shop-state {
            display: grid;
            min-height: 28rem;
            place-items: center;
            text-align: center;
        }

        .shop-demo-page .pagination .page-link {
            min-width: 3.8rem;
            height: 3.8rem;
        }

        @media (max-width: 991px) {
            .shop-demo-page .shop-banner .banner-content {
                padding: 3rem 2rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $selectedCategoryNames = $categories
            ->whereIn('id', $selectedCategories ?? [])
            ->pluck('name');
        $selectedBrandNames = $brands
            ->whereIn('id', $selectedBrands ?? [])
            ->pluck('name');
        $activeTitle = $selectedCategoryNames->isNotEmpty()
            ? $selectedCategoryNames->implode(', ')
            : 'Shop';
        $resultStart = $products->firstItem() ?? 0;
        $resultEnd = $products->lastItem() ?? 0;
        $resultTotal = $products->total();
    @endphp

    <main class="main shop-demo-page">
        <nav class="breadcrumb-nav">
            <div class="container">
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}"><i class="d-icon-home"></i></a></li>
                    <li>Shop</li>
                    @if ($selectedCategoryNames->isNotEmpty())
                        <li>{{ $selectedCategoryNames->implode(', ') }}</li>
                    @endif
                </ul>
            </div>
        </nav>

        <div class="page-content mb-10 pb-7">
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

                <div class="row main-content-wrap gutter-lg">
                    <aside class="col-lg-3 sidebar sidebar-fixed shop-sidebar sticky-sidebar-wrapper">
                        <div class="sidebar-overlay"></div>
                        <a class="sidebar-close" href="#"><i class="d-icon-times"></i></a>

                        <div class="sidebar-content">
                            <div class="sticky-sidebar">
                                <form id="shop-filter-form" method="GET" action="{{ route('shop.index') }}">
                                    <div class="widget widget-collapsible">
                                        <h3 class="widget-title">All Categories</h3>
                                        <ul class="widget-body filter-items search-ul">
                                            @foreach ($categories as $category)
                                                <li>
                                                    <label class="filter-link" for="category-{{ $category->id }}">
                                                        <input id="category-{{ $category->id }}" class="shop-filter-input" type="checkbox"
                                                            name="categories[]" value="{{ $category->id }}" @checked(in_array($category->id, $selectedCategories ?? []))>
                                                        <span>{{ $category->name }}</span>
                                                    </label>
                                                    <span class="filter-count">{{ $category->products_count }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="widget widget-collapsible">
                                        <h3 class="widget-title">Brands</h3>
                                        <ul class="widget-body filter-items">
                                            @foreach ($brands as $brand)
                                                <li>
                                                    <label class="filter-link" for="brand-{{ $brand->id }}">
                                                        <input id="brand-{{ $brand->id }}" class="shop-filter-input" type="checkbox"
                                                            name="brands[]" value="{{ $brand->id }}" @checked(in_array($brand->id, $selectedBrands ?? []))>
                                                        <span>{{ $brand->name }}</span>
                                                    </label>
                                                    <span class="filter-count">{{ $brand->products_count }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="widget widget-collapsible">
                                        <h3 class="widget-title">Filter by Price</h3>
                                        <div class="widget-body mt-3">
                                            <div class="price-inputs">
                                                <input type="number" class="form-control" name="min" min="0"
                                                    max="{{ $priceCeiling }}" value="{{ (int) $min_price }}" aria-label="Minimum price">
                                                <input type="number" class="form-control" name="max" min="0"
                                                    max="{{ $priceCeiling }}" value="{{ (int) $max_price }}" aria-label="Maximum price">
                                            </div>
                                            <div class="filter-actions mt-3">
                                                <div class="filter-price-text mb-3">
                                                    Price: <span>${{ number_format($min_price, 0) }} - ${{ number_format($max_price, 0) }}</span>
                                                </div>
                                                <button type="submit" class="btn btn-dark btn-rounded btn-filter">Filter</button>
                                                <a href="{{ route('shop.index') }}" class="btn btn-link btn-underline ml-3">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </aside>

                    <div class="col-lg-9 main-content">
                        <div class="shop-banner banner"
                            style="background-image: url('{{ asset('riode/images/demos/demo5/shop_banner.jpg') }}'); background-color: #efefef;">
                            <div class="banner-content">
                                <h1 class="banner-title ls-m text-uppercase">
                                    <strong class="mr-2">-50<sup>%</sup></strong>
                                    <span class="font-weight-normal"><strong class="d-block">{{ $activeTitle }}</strong>Sale</span>
                                </h1>
                                <h4 class="banner-subtitle text-body font-weight-normal">
                                    Fresh picks from the latest collection
                                </h4>
                                <a href="#products" class="btn btn-outline btn-dark btn-rounded">Shop now</a>
                            </div>
                        </div>

                        <nav class="toolbox sticky-toolbox sticky-content fix-top">
                            <div class="toolbox-left">
                                <a href="#" class="toolbox-item left-sidebar-toggle btn btn-sm btn-outline btn-primary btn-rounded d-lg-none">
                                    Filters<i class="d-icon-arrow-right"></i>
                                </a>
                                <div class="toolbox-item toolbox-sort select-box">
                                    <label for="shop-order">Sort By :</label>
                                    <select id="shop-order" name="order" class="form-control" form="shop-filter-form">
                                        <option value="-1" @selected($order === -1)>Default sorting</option>
                                        <option value="1" @selected($order === 1)>Sort by latest</option>
                                        <option value="2" @selected($order === 2)>Sort by oldest</option>
                                        <option value="3" @selected($order === 3)>Sort by price: low to high</option>
                                        <option value="4" @selected($order === 4)>Sort by price: high to low</option>
                                    </select>
                                </div>
                            </div>

                            <div class="toolbox-right">
                                <div class="toolbox-item toolbox-show select-box">
                                    <label for="shop-size">Show :</label>
                                    <select id="shop-size" name="size" class="form-control" form="shop-filter-form">
                                        @foreach ([12, 24, 36, 48] as $pageSize)
                                            <option value="{{ $pageSize }}" @selected((int) $size === $pageSize)>{{ $pageSize }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="toolbox-item toolbox-layout">
                                    <span class="d-icon-mode-grid btn-layout active"></span>
                                </div>
                            </div>
                        </nav>

                        @if ($selectedCategoryNames->isNotEmpty() || $selectedBrandNames->isNotEmpty())
                            <div class="mb-3">
                                @foreach ($selectedCategoryNames as $name)
                                    <span class="product-label label-new mr-1">{{ $name }}</span>
                                @endforeach
                                @foreach ($selectedBrandNames as $name)
                                    <span class="product-label label-sale mr-1">{{ $name }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div id="products" class="row cols-2 cols-sm-3 product-wrapper">
                            @forelse ($products as $product)
                                <div class="product-wrap">
                                    @include('partials.riode-product-card', ['product' => $product])
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="empty-shop-state">
                                        <div>
                                            <i class="d-icon-search" style="font-size: 5rem; color: #ccc;"></i>
                                            <h3 class="mt-3 mb-2">No products found</h3>
                                            <p class="mb-4">Try changing the selected filters.</p>
                                            <a href="{{ route('shop.index') }}" class="btn btn-dark btn-rounded">Clear Filters</a>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <nav class="toolbox toolbox-pagination">
                            <p class="show-info">
                                Showing <span>{{ $resultStart }}-{{ $resultEnd }} of {{ $resultTotal }}</span> Products
                            </p>

                            @if ($products->hasPages())
                                <ul class="pagination">
                                    <li class="page-item {{ $products->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link page-link-prev" href="{{ $products->previousPageUrl() ?: '#' }}" aria-label="Previous">
                                            <i class="d-icon-arrow-left"></i>Prev
                                        </a>
                                    </li>

                                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                        <li class="page-item {{ $page === $products->currentPage() ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endforeach

                                    <li class="page-item {{ $products->hasMorePages() ? '' : 'disabled' }}">
                                        <a class="page-link page-link-next" href="{{ $products->nextPageUrl() ?: '#' }}" aria-label="Next">
                                            Next<i class="d-icon-arrow-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            @endif
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        (function() {
            var filterForm = document.getElementById('shop-filter-form');

            if (!filterForm) {
                return;
            }

            function submitFilter() {
                filterForm.submit();
            }

            document.querySelectorAll('#shop-order, #shop-size, .shop-filter-input').forEach(function(control) {
                control.addEventListener('change', submitFilter);
            });

            document.querySelectorAll('.left-sidebar-toggle').forEach(function(toggle) {
                toggle.addEventListener('click', function(event) {
                    setTimeout(function() {
                        if (!document.body.classList.contains('sidebar-active')) {
                            event.preventDefault();
                            document.body.classList.add('sidebar-active');
                        }
                    }, 80);
                });
            });

            document.querySelectorAll('.shop-sidebar .sidebar-overlay, .shop-sidebar .sidebar-close').forEach(function(closeControl) {
                closeControl.addEventListener('click', function() {
                    document.body.classList.remove('sidebar-active');
                });
            });
        })();
    </script>
@endpush
