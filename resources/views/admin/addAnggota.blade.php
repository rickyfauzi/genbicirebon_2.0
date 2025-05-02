
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
                            <form action="{{route ('admin/user/saveanggota') }}" method="POST" enctype="multipart/form-data">
                                @csrf                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title student-info">Tambah Anggota</h5>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Nama Anggota<span class="login-danger">*</span></label>
                                            <select class="form-control select  @error('') is-invalid @enderror" name="nama" >
                                                <option selected disabled>Pilih Nama Anggota</option>
                                                @foreach ($Anggota as $name)
                                                <option value="{{ $name->name }}">{{ $name->name }}</option>
                                            @endforeach
                                            </select>
                                            @error('id_komisariat')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>    
                                

                                        
                                     <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Program Studi <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('prodi') is-invalid @enderror" name="prodi" placeholder="Masukan Prodi" value="{{ old('prodi') }}">
                                            @error('prodi')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                    <input type="hidden" class="image" name="image" value="photo_defaults.jpg">
                                    <div class="form-group local-forms">
                                        <label>Jenis Kelamin<span class="login-danger">*</span></label>
                                        <select class="form-control select @error('jk') is-invalid @enderror" name="jk" id="jk">
                                            <option selected disabled>Pilih Jenis Kelamin</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                        @error('jk')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    </div>
                                    </div>

                                    <div class="col-12 col-sm-4">
                                        <input type="hidden" class="image" name="image" value="photo_defaults.jpg">
                                        <div class="form-group local-forms">
                                            <label>Status Anggota<span class="login-danger">*</span></label>
                                            <select class="form-control select @error('status') is-invalid @enderror" name="status" id="status">
                                                <option selected disabled>Pilih Status Anggota</option>
                                                <option value="Active">Active</option>
                                                <option value="Existing">Existing</option>
                                            </select>
                                            @error('jk')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                        </div>
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <div class="form-group local-forms">
                                                <label>Nama Komisariat <span class="login-danger">*</span></label>
                                                <select class="form-control select  @error('') is-invalid @enderror" name="id_komisariat" >
                                                    <option selected disabled>Pilih Komisariat</option>
                                                    @foreach ($komisariat as $name)
                                                    <option value="{{ $name->id_komisariat }}">{{ $name->komisariat }}</option>
                                                @endforeach
                                                </select>
                                                @error('id_komisariat')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>    
                        
                                        <div class="col-12 col-sm-4">
                                            <div class="form-group local-forms">
                                                <label>ID User<span class="login-danger">*</span></label>
                                                <select class="form-control select  @error('id_user') is-invalid @enderror" name="id_user" >
                                                    <option selected disabled>Pilih ID User</option>
                                                    @foreach ($Anggota as $name)
                                                    <option value="{{ $name->id }}">{{ $name->id }} - {{ $name->name }}</option>
                                                @endforeach
                                                </select>
                                                @error('id_user')
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
                                            <button type="submit" class="btn btn-success" ><i class="fas fa-save"></i> Simpan</button>
                                            <a href="{{ route('admin/user/setanggota') }}" class="btn btn-danger"><i class="fas fa-backward"></i> Kembali</a>
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
