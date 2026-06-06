@extends('layouts.riode')

@section('title', 'Register - Riode Store')

@push('styles')
    <style>
        .account-page .login-popup {
            max-width: 54rem;
            margin: 0 auto;
            padding: 3.5rem 4rem 4rem;
            border: 1px solid #eee;
            box-shadow: 0 1rem 4rem rgba(0, 0, 0, .06);
        }

        .account-page .form-control {
            min-height: 4.6rem;
        }

        .account-page .invalid-feedback {
            display: block;
            margin-top: .6rem;
            color: #d26e4b;
            font-size: 1.2rem;
        }

        .account-page .form-checkbox {
            display: flex;
            align-items: center;
            gap: .8rem;
        }

        .account-page .custom-checkbox {
            width: 1.8rem;
            height: 1.8rem;
            border: 1px solid #ccc;
            -webkit-appearance: auto;
            appearance: auto;
        }

        .account-page .auth-link-row {
            margin-top: 2.5rem;
            color: #666;
        }

        @media (max-width: 575px) {
            .account-page .login-popup {
                padding: 2.5rem 2rem 3rem;
            }

            .account-page .form-footer {
                align-items: flex-start;
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
@endpush

@section('content')
    <main class="main account-page">
        <nav class="breadcrumb-nav">
            <div class="container">
                <ul class="breadcrumb">
                    <li><a href="{{ route('home.index') }}"><i class="d-icon-home"></i></a></li>
                    <li><a href="{{ route('shop.index') }}">Riode Shop</a></li>
                    <li>Create Account</li>
                </ul>
            </div>
        </nav>

        <div class="page-content mt-6 pb-10 mb-10">
            <div class="container">
                @if (session('status'))
                    <div class="alert alert-success alert-simple alert-inline mb-4">
                        <h4 class="alert-title">Success:</h4> {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-simple alert-inline mb-4">
                        <h4 class="alert-title">Error:</h4> {{ session('error') }}
                    </div>
                @endif

                <div class="login-popup">
                    <div class="form-box">
                        <div class="tab tab-nav-simple tab-nav-boxed form-tab">
                            <ul class="nav nav-tabs nav-fill align-items-center border-no justify-content-center mb-5" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link border-no lh-1 ls-normal" href="#signin">Login</a>
                                </li>
                                <li class="delimiter">or</li>
                                <li class="nav-item">
                                    <a class="nav-link active border-no lh-1 ls-normal" href="#register">Register</a>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane" id="signin">
                                    <form method="POST" action="{{ route('login') }}">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <input id="login-email" type="email" class="form-control @error('email') is-invalid @enderror"
                                                name="email" value="{{ old('email') }}" placeholder="Email Address *" required
                                                autocomplete="email">

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <input id="login-password" type="password" class="form-control @error('password') is-invalid @enderror"
                                                name="password" placeholder="Password *" required autocomplete="current-password">

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-footer">
                                            <div class="form-checkbox">
                                                <input type="checkbox" class="custom-checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-control-label" for="remember">Remember me</label>
                                            </div>

                                            @if (Route::has('password.request'))
                                                <a href="{{ route('password.request') }}" class="lost-link">Lost your password?</a>
                                            @endif
                                        </div>

                                        <button class="btn btn-dark btn-block btn-rounded" type="submit">Login</button>
                                    </form>

                                    <div class="form-choice text-center">
                                        <label class="ls-m">or Login With</label>
                                        <div class="social-links">
                                            <a href="#" class="social-link social-google fab fa-google border-no" title="Google"></a>
                                            <a href="#" class="social-link social-facebook fab fa-facebook-f border-no" title="Facebook"></a>
                                            <a href="#" class="social-link social-twitter fab fa-twitter border-no" title="Twitter"></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane active in" id="register">
                                    <form method="POST" action="{{ route('register') }}">
                                        @csrf

                                        <div class="form-group mb-3">
                                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror"
                                                name="name" value="{{ old('name') }}" placeholder="Name *" required autocomplete="name">

                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                                                name="email" value="{{ old('email') }}" placeholder="Your Email address *" required autocomplete="email">

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <input id="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror"
                                                name="mobile" value="{{ old('mobile') }}" placeholder="Mobile *" required>

                                            @error('mobile')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-3">
                                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                                name="password" placeholder="Password *" required autocomplete="new-password">

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <input id="password-confirm" type="password" class="form-control"
                                                name="password_confirmation" placeholder="Confirm Password *" required autocomplete="new-password">
                                        </div>

                                        <div class="form-footer">
                                            <div class="form-checkbox">
                                                <input type="checkbox" class="custom-checkbox" id="register-agree" required>
                                                <label class="form-control-label" for="register-agree">I agree to the privacy policy</label>
                                            </div>
                                        </div>

                                        <button class="btn btn-dark btn-block btn-rounded" type="submit">Register</button>
                                    </form>

                                    <div class="form-choice text-center">
                                        <label class="ls-m">or Register With</label>
                                        <div class="social-links">
                                            <a href="#" class="social-link social-google fab fa-google border-no" title="Google"></a>
                                            <a href="#" class="social-link social-facebook fab fa-facebook-f border-no" title="Facebook"></a>
                                            <a href="#" class="social-link social-twitter fab fa-twitter border-no" title="Twitter"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="auth-link-row text-center">
                    <span>Already have an account?</span>
                    <a href="{{ route('login') }}" class="btn btn-link btn-underline">Login to your account</a>
                </div>
            </div>
        </div>
    </main>
@endsection
