
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">            
            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card comman-shadow">
                        <div class="card-body">
                            <form action="{{route ('admin/user/save') }}" method="POST" enctype="multipart/form-data">
                                @csrf                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title student-info">Tambah User Akun</h5>
                                    </div>
                                 
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Nama Komisariat <span class="login-danger">*</span></label>
                                            <select class="form-control select  @error('id_komisariat') is-invalid @enderror" name="id_komisariat" >
                                                <option selected disabled>Pilih Komisariat</option>
                                                @foreach ($komisariat as $name)
                                                <option value="{{ $name->id_komisariat }}">{{ $name->komisariat }}</option>
                                            @endforeach
                                            </select>
                                            @error('jenis')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>    

                                        <div class="col-12 col-sm-4">
                                            <div class="form-group local-forms">
                                                <label>Nama Lengkap Pengguna <span class="login-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Masukan Nama Lengkap" >
                                                @error('nama_lengkap')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                     <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Email Pengguna <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" placeholder="Masukan Email Pengguna" value="{{ old('email') }}">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <input type="hidden" class="image" name="image" value="photo_defaults.jpg">
                                    <div class="form-group local-forms">
                                        <label>Role Name <span class="login-danger">*</span></label>
                                        <select class="form-control select @error('role_name') is-invalid @enderror" name="role_name" id="role_name">
                                            <option selected disabled>Role Type</option>
                                            @foreach ($role as $name)
                                                <option value="{{ $name->role_type }}">{{ $name->role_type }}</option>
                                            @endforeach
                                        </select>
                                        @error('role_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Password Pengguna <span class="login-danger">*</span></label>
                                            <input type="password" class="form-control pass-input @error('password') is-invalid @enderror" name="password" placeholder="Masukan Password">
                                            <span class="profile-views feather-eye toggle-password"></span>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                   
                        
                                    {{-- <input type="text" name="id_komisariat" value="{{ $id_komisariat }}" readonly hidden> --}}
                                    {{-- <input type="text" name="today" value="{{ $today }}" readonly hidden> --}}

                                    <div class="col-12">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary" >Simpan</button>
                                            <a href="{{ route('admin/user/anggota') }}" class="btn btn-danger">Kembali</a>
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
