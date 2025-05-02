
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
                            <form action={{route ('admin/anggota/update')}} method="POST" enctype="multipart/form-data">
                                @csrf                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title student-info">Edit Data Anggota</h5>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Nama Anggota<span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ $anggotas->nama }}" ">
                                            @error('nama')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>    
                                

                                        
                                     <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Program Studi <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('prodi') is-invalid @enderror" name="prodi" value="{{ $anggotas->prodi }}" ">
                                            @error('prodi')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                    
                                    <div class="form-group local-forms">
                                        <label>Jenis Kelamin<span class="login-danger">*</span></label>
                                        <select class="form-control select @error('jk') is-invalid @enderror" name="jk" id="jk">
                                            <option disabled>Pilih Jenis Kegiatan</option>
                                            <option value="L" {{ $anggotas->jk == 'L' ? "selected" :"L"}}>L</option>
                                            <option value="P" {{ $anggotas->jk == 'P' ? "selected" :"P"}}>P</option>
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
                                            <label>Status Anggota<span class="login-danger">*</span></label>
                                            <select class="form-control select @error('status') is-invalid @enderror" name="status" id="status">
                                                <option disabled>Pilih Status Anggota</option>
                                                <option value="Active" {{ $anggotas->status == 'Active' ? "selected" :"Active"}}>Active</option>
                                                <option value="Existing" {{ $anggotas->status == 'Existing' ? "selected" :"Existing"}}>Existing</option>
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
                                                <select class="form-control select  @error('id_komisariat') is-invalid @enderror" name="id_komisariat" >

                                                    @foreach ($anggotass as $name)
                                                    <option value="{{$name->id_komisariat}}" {{ $name->id_komisariat}} == {{$name->id_komisariat}} ? "selected" : {{$name->komisariat}}>{{$name->komisariat}}</option>
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
                                                   
                                                    @foreach ($anggotass as $name)
                                                    <option value="{{$name->id_user}}" {{ $name->id_user}} == {{$name->id_user}} ? "selected" : {{$name->id_user}}>{{$name->id_user}} - {{$name->nama}}</option>
                                                @endforeach
                                                </select>
                                                @error('id_user')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>    
                        
                                         <input type="text" name="id_anggota" id="id_anggota" value="{{ $anggotas->id_anggota }}" readonly hidden>
                                  

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
