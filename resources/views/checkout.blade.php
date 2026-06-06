@extends('layouts.riode')

@section('title', 'Checkout - Riode Store')
@section('body_class', 'checkout')

@push('styles')
    <style>
        .checkout-page .checkout-panel,
        .checkout-page .summary {
            border: 1px solid #e6e6e6;
            border-radius: 4px;
            background: #fff;
        }

        .checkout-page .checkout-panel {
            padding: 2.8rem;
            margin-bottom: 2rem;
        }

        .checkout-page .checkout-panel-title {
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }

        .checkout-page .saved-address {
            display: grid;
            gap: .8rem;
            padding: 1.8rem;
            border: 1px solid #e5e5e5;
            background: #fafafa;
            color: #555;
        }

        .checkout-page .saved-address strong {
            color: #222;
            font-size: 1.5rem;
        }

        .checkout-page .form-control {
            min-height: 4.5rem;
            border-color: #e1e1e1;
        }

        .checkout-page .form-group label {
            display: block;
            margin-bottom: .7rem;
            color: #222;
            font-weight: 600;
        }

        .checkout-page .payment-option {
            display: flex;
            align-items: flex-start;
            gap: 1.2rem;
            padding: 1.6rem;
            border: 1px solid #e6e6e6;
        }

        .checkout-page .payment-option + .payment-option {
            margin-top: 1rem;
        }

        .checkout-page .payment-option input {
            margin-top: .4rem;
        }

        .checkout-page .payment-option strong {
            display: block;
            color: #222;
        }

        .checkout-page .payment-option span {
            display: block;
            margin-top: .3rem;
            color: #777;
            font-size: 1.3rem;
            line-height: 1.5;
        }

        .checkout-page .order-table td {
            padding: 1.2rem 0;
            vertical-align: top;
        }

        .checkout-page .order-item-meta {
            margin-top: .4rem;
            color: #888;
            font-size: 1.2rem;
        }

        .checkout-page .summary {
            padding: 2.6rem;
        }

        .checkout-page .summary-title {
            margin-bottom: 1.8rem;
        }

        .checkout-page .total td {
            padding-top: 1.2rem;
        }

        .checkout-page .stripe-note {
            margin: 1.4rem 0 0;
            color: #777;
            font-size: 1.25rem;
            line-height: 1.5;
        }

        @media (max-width: 767px) {
            .checkout-page .checkout-panel,
            .checkout-page .summary {
                padding: 2rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $items = Cart::instance('cart')->content();
        $money = fn ($value) => '$' . number_format((float) str_replace(',', '', (string) $value), 2);
        $hasDiscount = Session::has('discounts');
    @endphp

    <main class="main checkout-page">
        <nav class="breadcrumb-nav">
            <div class="container">
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}"><i class="d-icon-home"></i></a></li>
                    <li><a href="{{ route('cart.index') }}">Cart</a></li>
                    <li>Checkout</li>
                </ul>
            </div>
        </nav>

        <div class="page-content pt-7 pb-10">
            <div class="step-by pr-4 pl-4">
                <h3 class="title title-simple title-step"><a href="{{ route('cart.index') }}">1. Shopping Cart</a></h3>
                <h3 class="title title-simple title-step active"><a href="{{ route('cart.checkout') }}">2. Checkout</a></h3>
                <h3 class="title title-simple title-step"><span>3. Order Complete</span></h3>
            </div>

            <div class="container mt-7">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('cart.place.on.order') }}" method="POST" class="js-backend-form">
                    @csrf
                    <div class="row gutter-lg">
                        <div class="col-lg-7">
                            <section class="checkout-panel">
                                <h2 class="checkout-panel-title">Shipping Details</h2>

                                @if ($address)
                                    <div class="saved-address">
                                        <strong>{{ $address->name }}</strong>
                                        <span>{{ $address->address }}</span>
                                        @if ($address->locality)
                                            <span>{{ $address->locality }}</span>
                                        @endif
                                        <span>{{ $address->city }}, {{ $address->state }} {{ $address->zip }}</span>
                                        <span>{{ $address->country }}</span>
                                        @if ($address->landmark)
                                            <span>{{ $address->landmark }}</span>
                                        @endif
                                        <span>{{ $address->phone }}</span>
                                    </div>
                                @else
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Full Name *</label>
                                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone">Phone Number *</label>
                                                <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone') }}" required>
                                                @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="address">Street Address *</label>
                                                <input type="text" id="address" name="address" class="form-control" value="{{ old('address') }}" required>
                                                @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="locality">Apartment, Area or Locality *</label>
                                                <input type="text" id="locality" name="locality" class="form-control" value="{{ old('locality') }}" required>
                                                @error('locality')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="city">Town / City *</label>
                                                <input type="text" id="city" name="city" class="form-control" value="{{ old('city') }}" required>
                                                @error('city')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="state">State *</label>
                                                <input type="text" id="state" name="state" class="form-control" value="{{ old('state') }}" required>
                                                @error('state')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="zip">ZIP / Postal Code *</label>
                                                <input type="text" id="zip" name="zip" class="form-control" value="{{ old('zip') }}" required>
                                                @error('zip')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="country">Country</label>
                                                <input type="text" id="country" name="country" class="form-control" value="{{ old('country', 'United States') }}">
                                                @error('country')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="landmark">Landmark</label>
                                                <input type="text" id="landmark" name="landmark" class="form-control" value="{{ old('landmark') }}">
                                                @error('landmark')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </section>

                            <section class="checkout-panel">
                                <h2 class="checkout-panel-title">Payment Method</h2>
                                <label class="payment-option" for="mode-card">
                                    <input type="radio" name="mode" id="mode-card" value="card" {{ old('mode', 'card') === 'card' ? 'checked' : '' }}>
                                    <span>
                                        <strong>Credit or Debit Card</strong>
                                        <span>Pay securely through Stripe Checkout. You will be redirected to Stripe to complete payment.</span>
                                    </span>
                                </label>
                                <label class="payment-option" for="mode-cod">
                                    <input type="radio" name="mode" id="mode-cod" value="cod" {{ old('mode') === 'cod' ? 'checked' : '' }}>
                                    <span>
                                        <strong>Cash on Delivery</strong>
                                        <span>Place the order now and pay when your package arrives.</span>
                                    </span>
                                </label>
                                @error('mode')<small class="text-danger d-block mt-2">{{ $message }}</small>@enderror
                            </section>
                        </div>

                        <aside class="col-lg-5 sticky-sidebar-wrapper">
                            <div class="sticky-sidebar" data-sticky-options="{'bottom': 20}">
                                <div class="summary">
                                    <h3 class="summary-title text-left">Your Order</h3>
                                    <table class="order-table w-100">
                                        <tbody>
                                            @foreach ($items as $item)
                                                @php
                                                    $itemOptions = collect($item->options)->filter(fn ($value) => filled($value));
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->name }}</strong> x {{ $item->qty }}
                                                        @if ($itemOptions->isNotEmpty())
                                                            <div class="order-item-meta">
                                                                @foreach ($itemOptions as $optionName => $optionValue)
                                                                    {{ \Illuminate\Support\Str::headline($optionName) }}: {{ $optionValue }}{{ ! $loop->last ? ', ' : '' }}
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">{{ $money($item->price * $item->qty) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                    <table class="total w-100 mt-3">
                                        <tbody>
                                            <tr>
                                                <td><h4 class="summary-subtitle">Subtotal</h4></td>
                                                <td class="text-right">{{ $money(Cart::instance('cart')->subtotal()) }}</td>
                                            </tr>
                                            @if ($hasDiscount)
                                                <tr>
                                                    <td>Discount {{ Session::get('coupon.code') }}</td>
                                                    <td class="text-right">-{{ $money(Session::get('discounts.discount')) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>After Discount</td>
                                                    <td class="text-right">{{ $money(Session::get('discounts.subtotal')) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>VAT</td>
                                                    <td class="text-right">{{ $money(Session::get('discounts.tax')) }}</td>
                                                </tr>
                                                <tr class="summary-subtotal">
                                                    <td><h4 class="summary-subtitle">Total</h4></td>
                                                    <td class="text-right"><p class="summary-total-price ls-s">{{ $money(Session::get('discounts.total')) }}</p></td>
                                                </tr>
                                            @else
                                                <tr>
                                                    <td>Shipping</td>
                                                    <td class="text-right">Free</td>
                                                </tr>
                                                <tr>
                                                    <td>VAT</td>
                                                    <td class="text-right">{{ $money(Cart::instance('cart')->tax()) }}</td>
                                                </tr>
                                                <tr class="summary-subtotal">
                                                    <td><h4 class="summary-subtitle">Total</h4></td>
                                                    <td class="text-right"><p class="summary-total-price ls-s">{{ $money(Cart::instance('cart')->total()) }}</p></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>

                                    <button type="submit" class="btn btn-dark btn-rounded btn-checkout btn-block mt-3">Place Order</button>
                                    <p class="stripe-note">Card payments are completed on Stripe. Your cart is only cleared after the payment is verified.</p>
                                </div>
                            </div>
                        </aside>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection
