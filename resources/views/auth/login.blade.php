@extends('layouts.login')

@section('content')
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card p-5 shadow">
                <div class="card-header text-white py-3">
                    <div class="d-flex justify-content-center align-items-center w-100 header-images">
                        <!-- Left Image -->
                        <div>
                            <img src="{{ asset('images/cdo-seal.png') }}" alt="City Logo" style="height: 90px;">
                        </div>

                        <!-- Right Image -->
                        <div>
                            <img src="{{ asset('images/footer/risev2.png') }}" alt="RISE Logo" style="height: 90px;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h1 class="text-center py-5 flex-grow-1 px-3" style="font-size: clamp(1.5rem, 5vw, 2.8rem); color: #0E4A84;">
                            {{ __('DOCUMENT MANAGEMENT SYSTEM') }}
                        </h1>
                    </div>
                </div>

                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label">{{ __('Username') }}</label>
                            <input id="username" type="text" class="form-control @error('username') is-invalid @enderror"
                                name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                            @error('username')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                                name="password" required autocomplete="current-password">

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <!-- <div class="mb-3 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                {{ __('Remember Me') }}
                            </label>
                        </div> -->

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-block py-2">
                                {{ __('Login') }}
                            </button>
                        </div>

                        @if (Route::has('password.request'))
                        <!-- <div class="text-center">
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        </div> -->
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection