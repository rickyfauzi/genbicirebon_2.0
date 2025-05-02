
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
                            <form action="{{ route('anggota/absen-anggota/save') }}" method="POST" enctype="multipart/form-data">
                                @csrf                                
                                <div class="row">
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Nama Kegiatan <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" name="nama_kegiatan" placeholder="Masukan Nama Kegiatan" value="{{ old('nama_kegiatan') }}">
                                            @error('nama_kegiatan')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Tanggal Pelaksanaan <span class="login-danger">*</span></label>
                                            <input type="date" class="form-control @error('tgl_pelaksanaan') is-invalid @enderror" name="tgl_pelaksanaan" value="{{ old('tgl_pelaksanaan') }}">
                                            @error('tgl_pelaksanaan')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Jenis Kegiatan <span class="login-danger">*</span></label>
                                            <select class="form-control select  @error('jenis') is-invalid @enderror" name="jenis">
                                                <option selected disabled>Pilih Jenis Kegiatan</option>
                                                <option value="Besar" {{ old('jenis') == 'Besar' ? "selected" : "Besar"}}>Besar</option>
                                                <option value="Sedang" {{ old('jenis') == 'Sedang' ? "selected" : "Sedang"}}>Sedang</option>                                         
                                                <option value="Kecil" {{ old('jenis') == 'Kecil' ? "selected" : "Kecil"}}>Kecil</option>                                            
                                            </select>
                                            @error('jenis')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>      
                        
                                    <input type="text" name="id_komisariat" value="{{ $id_komisariat }}" readonly hidden>
                                    <input type="text" name="today" value="{{ $today }}" readonly hidden>

                                    <div class="col-12">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary">Simpan</button>
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
