@extends('layouts.app')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="login-right">
        <div class="login-right-wrap">
            <h3 class="text-center"><b>SATU GenBI</b></h3>
            {{-- <h6 class="page-title text-center">Sistem Absensi Terpadu</h6> --}}
            {{-- <br> --}}
            <h2 class="page-title text-center">Sign In</h2>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Email<span class="login-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email">
                    <span class="profile-views"><i class="fas fa-envelope"></i></span>
                </div>
                <div class="form-group">
                    <label>Password <span class="login-danger">*</span></label>
                    <input type="password" class="form-control pass-input @error('password') is-invalid @enderror"
                        name="password">
                    <span class="profile-views feather-eye toggle-password"></span>
                </div>
                <div class="forgotpass">
                    <div class="remember-me">
                        <label class="custom_check mr-2 mb-0 d-inline-flex remember-me"> Remember Me
                            <input type="checkbox" name="radio">
                            <span class="checkmark"></span>
                        </label>
                    </div>
                    <a href="{{ route('password.email') }}">Forgot Password?</a>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary btn-block" type="submit">Login</button>
                </div>
            </form>

            <!-- Sign In with buttons -->
            <div class="text-center">
                <p>or sign in with:</p>
                {{-- <button type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-facebook-f"></i>
                </button> --}}

                <button type="button" data-mdb-button-init data-mdb-ripple-init
                    class="btn btn-danger btn-floating mx-1 btn-block">
                    <i class="fab fa-google"></i>
                    <span> Google</span>
                </button>

                {{-- <button type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-twitter"></i>
                </button> --}}

                {{-- <button type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-link btn-floating mx-1">
                    <i class="fab fa-github"></i> --}}
                </button>
            </div>

            {{-- <div class="form-group">
                <a class="btn btn-outline-dark btn-block" href="https://genbicirebon.org">Website GenBI</a>
            </div> --}}


        </div>
    </div>
@endsection
