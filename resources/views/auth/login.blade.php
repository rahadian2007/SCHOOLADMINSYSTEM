@extends('dashboard.authBase')

@section('content')

    <div class="container text-center">
      <div class="d-flex align-items-center justify-content-center my-4">
        <img src="/assets/brand/logo.png" width="32" height="32" />
        <h5 class="ml-2 font-weight-bold mb-0">Yayasan Al Haq Margahayu</h5>
      </div>
      <img src="/svg/illustration-dashboard.svg" width="234" />
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card-group">
            <div class="card p-4">
              <div class="card-body">
                <h2>Selamat Datang</h2>
                <p class="mb-4">Silakan masukkan email dan password Anda untuk melanjutkan</p>
                
                <form method="POST" action="{{ route('login') }}">
                  @csrf
                  <div class="input-group mb-3">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <i class="cil-envelope-closed"></i>
                      </span>
                    </div>
                    <input class="form-control" type="text" placeholder="{{ __('Email Address') }}" name="email" value="{{ old('email') }}" required autofocus>
                  </div>

                  <div class="input-group mb-4">
                    <div class="input-group-prepend">
                      <span class="input-group-text">
                        <svg class="c-icon">
                          <use xlink:href="assets/icons/coreui/free-symbol-defs.svg#cui-lock-locked"></use>
                        </svg>
                      </span>
                    </div>
                    <input class="form-control" type="password" placeholder="{{ __('Password') }}" name="password" required>
                  </div>

                  <div class="row">
                    <div class="col-6 text-left">
                        <button class="btn btn-primary px-4" type="submit">{{ __('Login') }}</button>
                    </div>
                    <div class="col-6 text-right">
                      <a href="{{ route('password.request') }}" class="btn btn-link px-0">{{ __('Forgot Your Password?') }}</a>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

@endsection

@section('javascript')

@endsection