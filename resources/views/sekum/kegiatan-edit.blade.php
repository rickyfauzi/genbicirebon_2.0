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
                            <form action="{{ route('sekum/kegiatan/update') }}" method="POST" enctype="multipart/form-data">
                                @csrf                                
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title student-info">Edit Kegiatan</h5>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Nama kegiatan <span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" name="nama_kegiatan" value="{{ $sekumKegiatanEdit->nama_kegiatan }}">
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
                                            <input class="form-control @error('tgl_pelaksanaan') is-invalid @enderror" name="tgl_pelaksanaan" type="date"  value="{{ $sekumKegiatanEdit->tgl_pelaksanaan }}">
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
                                                <option value="Utama" {{ $sekumKegiatanEdit->jenis == 'Utama' ? "selected" :"Utama"}}>Utama</option>
                                                <option value="Tambahan" {{ $sekumKegiatanEdit->jenis == 'Tambahan' ? "selected" :"Tambahan"}}>Tambahan</option>
                                            </select>
                                            @error('jenis')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <input type="text" name="id_kegiatan" value="{{ $sekumKegiatanEdit->id_kegiatan }}" readonly hidden>
                                    <input type="text" name="id_komisariat" value="{{ $id_komisariat }}" readonly hidden>
                                    <input type="text" name="updated_at" value="{{ $today }}" readonly hidden>

                                    <div class="col-12">
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
