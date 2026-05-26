<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Riode Store')</title>

    <meta name="keywords" content="Laravel ecommerce, fashion store, Riode">
    <meta name="description" content="Riode style ecommerce storefront">
    <meta name="author" content="{{ config('app.name', 'Laravel') }}">

    <link rel="icon" type="image/png" href="{{ asset('riode/images/icons/favicon.png') }}">
    <link rel="preload" href="{{ asset('riode/fonts/riode115b.ttf?5gap68') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ asset('riode/vendor/fontawesome-free/webfonts/fa-solid-900.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">
    <link rel="preload" href="{{ asset('riode/vendor/fontawesome-free/webfonts/fa-brands-400.woff2') }}" as="font" type="font/woff2" crossorigin="anonymous">

    <script>
        WebFontConfig = {
            google: {
                families: ['Poppins:300,400,500,600,700,800']
            }
        };
        (function(d) {
            var wf = d.createElement('script'), s = d.scripts[0];
            wf.src = '{{ asset('riode/js/webfont.js') }}';
            wf.async = true;
            s.parentNode.insertBefore(wf, s);
        })(document);
    </script>

    <link rel="stylesheet" type="text/css" href="{{ asset('riode/vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('riode/vendor/animate/animate.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('riode/vendor/magnific-popup/magnific-popup.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('riode/vendor/owl-carousel/owl.carousel.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('riode/vendor/sticky-icon/stickyicon.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('riode/css/style.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('riode/css/demo1.min.css') }}">
    @stack('styles')
</head>

@php
    $navCategories = collect($categories ?? []);

    if ($navCategories->isEmpty()) {
        try {
            $navCategories = \App\Models\Category::orderBy('name')->take(4)->get();
        } catch (\Throwable $exception) {
            $navCategories = collect();
        }
    } else {
        $navCategories = $navCategories->take(4);
    }

    try {
        $cartCount = \Surfsidemedia\Shoppingcart\Facades\Cart::instance('cart')->content()->count();
        $wishlistCount = \Surfsidemedia\Shoppingcart\Facades\Cart::instance('wishlist')->content()->count();
        $cartSubtotal = \Surfsidemedia\Shoppingcart\Facades\Cart::instance('cart')->subtotal();
    } catch (\Throwable $exception) {
        $cartCount = 0;
        $wishlistCount = 0;
        $cartSubtotal = '0.00';
    }
@endphp

<body class="home">
    <div class="page-wrapper">
        <h1 class="d-none">Riode Ecommerce Store</h1>

        <header class="header">
            <div class="header-top">
                <div class="container">
                    <div class="header-left">
                        <p class="welcome-msg">Welcome to Riode fashion store.</p>
                    </div>
                    <div class="header-right">
                        <a href="{{ route('home.contact') }}" class="contact d-lg-show"><i class="d-icon-map"></i>Contact</a>
                        <a href="{{ route('home.about') }}" class="help d-lg-show"><i class="d-icon-info"></i>About</a>
                        @guest
                            <a href="{{ route('login') }}" class="login-toggle d-md-show"><i class="d-icon-user"></i>Sign in</a>
                            <span class="delimiter">/</span>
                            <a href="{{ route('register') }}" class="register-toggle d-md-show ml-0">Register</a>
                        @else
                            <a href="{{ Auth::user()->utype === 'ADM' ? route('admin.index') : route('user.index') }}" class="login-toggle d-md-show">
                                <i class="d-icon-user"></i>{{ Auth::user()->name }}
                            </a>
                        @endguest
                    </div>
                </div>
            </div>

            <div class="header-middle sticky-header fix-top sticky-content">
                <div class="container">
                    <div class="header-left">
                        <a href="#" class="mobile-menu-toggle">
                            <i class="d-icon-bars2"></i>
                        </a>
                        <a href="{{ route('home.index') }}" class="logo">
                            <img src="{{ asset('riode/images/logo.png') }}" alt="Riode logo" width="153" height="44">
                        </a>

                        <div class="header-search hs-simple">
                            <form action="{{ route('shop.index') }}" class="input-wrapper">
                                <input type="text" class="form-control" name="query" autocomplete="off" placeholder="Search..." required>
                                <button class="btn btn-search" type="submit" title="Search">
                                    <i class="d-icon-search"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="header-right">
                        <a href="tel:+1800123456" class="icon-box icon-box-side">
                            <div class="icon-box-icon mr-0 mr-lg-2">
                                <i class="d-icon-phone"></i>
                            </div>
                            <div class="icon-box-content d-lg-show">
                                <h4 class="icon-box-title">Call Us Now:</h4>
                                <p>0(800) 123-456</p>
                            </div>
                        </a>
                        <span class="divider"></span>
                        <a href="{{ route('wishlist.index') }}" class="wishlist-toggle">
                            <i class="d-icon-heart"></i>
                            @if ($wishlistCount > 0)
                                <span class="cart-count">{{ $wishlistCount }}</span>
                            @endif
                        </a>
                        <span class="divider"></span>
                        <div class="dropdown cart-dropdown type2 off-canvas mr-0 mr-lg-2">
                            <a href="{{ route('cart.index') }}" class="cart-toggle label-block link">
                                <div class="cart-label d-lg-show">
                                    <span class="cart-name">Shopping Cart:</span>
                                    <span class="cart-price">${{ $cartSubtotal }}</span>
                                </div>
                                <i class="d-icon-bag"><span class="cart-count">{{ $cartCount }}</span></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="header-bottom d-lg-show">
                <div class="container">
                    <div class="header-left">
                        <nav class="main-nav">
                            <ul class="menu">
                                <li class="{{ request()->routeIs('home.index') ? 'active' : '' }}">
                                    <a href="{{ route('home.index') }}">Home</a>
                                </li>
                                <li class="{{ request()->routeIs('shop.*') ? 'active' : '' }}">
                                    <a href="{{ route('shop.index') }}">Categories</a>
                                    <div class="megamenu">
                                        <div class="row">
                                            <div class="col-6 col-sm-4 col-md-4 col-lg-3">
                                                <h4 class="menu-title">Shop Categories</h4>
                                                <ul>
                                                    @forelse ($navCategories as $category)
                                                        <li><a href="{{ route('shop.index', ['categories' => $category->id]) }}">{{ $category->name }}</a></li>
                                                    @empty
                                                        <li><a href="{{ route('shop.index') }}">All Products</a></li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-4 col-md-4 col-lg-3">
                                                <h4 class="menu-title">Store Links</h4>
                                                <ul>
                                                    <li><a href="{{ route('shop.index') }}">Shop All</a></li>
                                                    <li><a href="{{ route('wishlist.index') }}">Wishlist</a></li>
                                                    <li><a href="{{ route('cart.index') }}">Cart</a></li>
                                                    <li><a href="{{ route('cart.checkout') }}">Checkout</a></li>
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-4 col-md-4 col-lg-3">
                                                <h4 class="menu-title">Customer</h4>
                                                <ul>
                                                    <li><a href="{{ route('home.about') }}">About Us</a></li>
                                                    <li><a href="{{ route('home.contact') }}">Contact Us</a></li>
                                                    @auth
                                                        <li><a href="{{ route('user.index') }}">My Account</a></li>
                                                        <li><a href="{{ route('user.orders') }}">Orders</a></li>
                                                    @else
                                                        <li><a href="{{ route('login') }}">Login</a></li>
                                                        <li><a href="{{ route('register') }}">Register</a></li>
                                                    @endauth
                                                </ul>
                                            </div>
                                            <div class="col-6 col-sm-4 col-md-4 col-lg-3 menu-banner menu-banner1 banner banner-fixed">
                                                <figure>
                                                    <img src="{{ asset('riode/images/menu/banner-1.jpg') }}" alt="Menu banner" width="221" height="330">
                                                </figure>
                                                <div class="banner-content y-50">
                                                    <h4 class="banner-subtitle font-weight-bold text-primary ls-m">Sale.</h4>
                                                    <h3 class="banner-title font-weight-bold"><span class="text-uppercase">Up to</span>70% Off</h3>
                                                    <a href="{{ route('shop.index') }}" class="btn btn-link btn-underline">shop now<i class="d-icon-arrow-right"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li><a href="{{ route('shop.index') }}">Products</a></li>
                                <li><a href="{{ route('home.about') }}">About Us</a></li>
                                <li><a href="{{ route('home.contact') }}">Contact</a></li>
                            </ul>
                        </nav>
                    </div>
                    <div class="header-right">
                        <a href="{{ route('shop.index') }}"><i class="d-icon-card"></i>Special Offers</a>
                        <a href="{{ route('shop.index') }}" class="ml-6">Shop Riode</a>
                    </div>
                </div>
            </div>
        </header>

        @yield('content')

        <footer class="footer">
            <div class="container">
                <div class="footer-top">
                    <div class="row align-items-center">
                        <div class="col-lg-3">
                            <a href="{{ route('home.index') }}" class="logo-footer">
                                <img src="{{ asset('riode/images/logo-footer.png') }}" alt="Riode footer logo" width="154" height="43">
                            </a>
                        </div>
                        <div class="col-lg-9">
                            <div class="widget widget-newsletter form-wrapper form-wrapper-inline">
                                <div class="newsletter-info mx-auto mr-lg-2 ml-lg-4">
                                    <h4 class="widget-title">Subscribe to our Newsletter</h4>
                                    <p>Get the latest product updates, sales and offers.</p>
                                </div>
                                <form action="#" class="input-wrapper input-wrapper-inline">
                                    <input type="email" class="form-control" name="email" placeholder="Email address here..." required>
                                    <button class="btn btn-primary btn-rounded btn-md ml-2" type="submit">subscribe<i class="d-icon-arrow-right"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-middle">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="widget widget-info">
                                <h4 class="widget-title">Contact Info</h4>
                                <ul class="widget-body">
                                    <li><label>Phone:</label> <a href="tel:+11234567890">Toll Free (123) 456-7890</a></li>
                                    <li><label>Email:</label> <a href="mailto:support@example.com">support@example.com</a></li>
                                    <li><label>Address:</label> <a href="#">123 Street Name, City</a></li>
                                    <li><label>WORKING DAYS / HOURS:</label></li>
                                    <li><a href="#">Mon - Sun / 9:00 AM - 8:00 PM</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="widget ml-lg-4">
                                <h4 class="widget-title">My Account</h4>
                                <ul class="widget-body">
                                    <li><a href="{{ route('home.about') }}">About Us</a></li>
                                    <li><a href="{{ route('cart.index') }}">View Cart</a></li>
                                    <li><a href="{{ route('wishlist.index') }}">Wishlist</a></li>
                                    <li><a href="{{ route('home.contact') }}">Customer Service</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="widget ml-lg-4">
                                <h4 class="widget-title">Store</h4>
                                <ul class="widget-body">
                                    <li><a href="{{ route('shop.index') }}">Shop</a></li>
                                    <li><a href="{{ route('cart.checkout') }}">Checkout</a></li>
                                    <li><a href="{{ route('login') }}">Sign in</a></li>
                                    <li><a href="{{ route('home.contact') }}">Help</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="widget widget-instagram">
                                <h4 class="widget-title">Instagram</h4>
                                <figure class="widget-body row">
                                    @for ($i = 1; $i <= 8; $i++)
                                        <div class="col-3">
                                            <img src="{{ asset('riode/images/instagram/' . str_pad($i, 2, '0', STR_PAD_LEFT) . '.jpg') }}" alt="Instagram {{ $i }}" width="64" height="64">
                                        </div>
                                    @endfor
                                </figure>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer-bottom">
                    <div class="footer-left">
                        <figure class="payment">
                            <img src="{{ asset('riode/images/payment.png') }}" alt="payment" width="159" height="29">
                        </figure>
                    </div>
                    <div class="footer-center">
                        <p class="copyright">Riode eCommerce &copy; {{ date('Y') }}. All Rights Reserved</p>
                    </div>
                    <div class="footer-right">
                        <div class="social-links">
                            <a href="#" title="Facebook" class="social-link social-facebook fab fa-facebook-f"></a>
                            <a href="#" title="Twitter" class="social-link social-twitter fab fa-twitter"></a>
                            <a href="#" title="LinkedIn" class="social-link social-linkedin fab fa-linkedin-in"></a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <div class="sticky-footer sticky-content fix-bottom">
        <a href="{{ route('home.index') }}" class="sticky-link">
            <i class="d-icon-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('shop.index') }}" class="sticky-link">
            <i class="d-icon-volume"></i>
            <span>Categories</span>
        </a>
        <a href="{{ route('wishlist.index') }}" class="sticky-link">
            <i class="d-icon-heart"></i>
            <span>Wishlist</span>
        </a>
        <a href="{{ route('login') }}" class="sticky-link">
            <i class="d-icon-user"></i>
            <span>Account</span>
        </a>
    </div>

    <a id="scroll-top" href="#top" title="Top" role="button" class="scroll-top"><i class="d-icon-arrow-up"></i></a>

    <div class="mobile-menu-wrapper">
        <div class="mobile-menu-overlay"></div>
        <a class="mobile-menu-close" href="#"><i class="d-icon-times"></i></a>
        <div class="mobile-menu-container scrollable">
            <form action="{{ route('shop.index') }}" class="input-wrapper">
                <input type="text" class="form-control" name="query" autocomplete="off" placeholder="Search your keyword..." required>
                <button class="btn btn-search" type="submit" title="Search">
                    <i class="d-icon-search"></i>
                </button>
            </form>
            <ul class="mobile-menu mmenu-anim">
                <li><a href="{{ route('home.index') }}">Home</a></li>
                <li>
                    <a href="{{ route('shop.index') }}">Categories</a>
                    @if ($navCategories->isNotEmpty())
                        <ul>
                            @foreach ($navCategories as $category)
                                <li><a href="{{ route('shop.index', ['categories' => $category->id]) }}">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </li>
                <li><a href="{{ route('shop.index') }}">Products</a></li>
                <li><a href="{{ route('home.about') }}">About</a></li>
                <li><a href="{{ route('home.contact') }}">Contact</a></li>
                <li><a href="{{ route('cart.index') }}">Cart</a></li>
            </ul>
        </div>
    </div>

    <script src="{{ asset('riode/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('riode/vendor/parallax/parallax.min.js') }}"></script>
    <script src="{{ asset('riode/vendor/imagesloaded/imagesloaded.pkgd.min.js') }}"></script>
    <script src="{{ asset('riode/vendor/elevatezoom/jquery.elevatezoom.min.js') }}"></script>
    <script src="{{ asset('riode/vendor/magnific-popup/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('riode/vendor/owl-carousel/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('riode/js/main.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
