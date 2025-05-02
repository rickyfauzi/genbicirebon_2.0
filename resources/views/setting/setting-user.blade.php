
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">SETTING AKUN</h3>
                            <ul class="breadcrumb">                                
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card comman-shadow">
                        <div class="card-body">
                            <form action="{{ route('setting/update-user')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" class="form-control" name="id" value="{{ $settingUser->id }}" readonly>
                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title student-info">Update Akun User
                                            <span>
                                                <a href="javascript:;"><i class="feather-more-vertical"></i></a>
                                            </span>
                                        </h5>
                                    </div>
                                    <div class="col-12 p-2 pb-5 text-center">
                                        <span class="w-50">
                                            <img class="rounded-circle" src="/images/{{ Session::get('avatar') }}" width="100px" alt="{{ Session::get('name') }}">
                                            <div class="user-text p-2">
                                                <h5>{{ Session::get('name') }}</h5> 
                                                <p class="text-muted mb-0">{{ Session::get('role_name') }}</p>
                                            </div>
                                        </span>
                                    </div>
                                    <div class="col-12 col-sm-7">
                                        <div class="form-group local-forms">
                                            <label>Ganti Nama Lengkap <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ $settingUser->name }}">
                                            @error('username')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-8 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Ganti Email <span class="login-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $settingUser->email }}">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-7">
                                        <div class="form-group local-forms">
                                            <label>Ganti Password <span class="login-danger">*</span></label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="******"  required>
                                            <span class="profile-views feather-eye toggle-password"></span>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12  text-right">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
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
