@extends('layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Edit Kegiatan</h3>
                            <ul class="breadcrumb">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <span><i class="fas fa-tasks"></i> Kegiatan</span>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Message --}}
            {!! Toastr::message() !!}

            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <form action="{{ route('admin.kegiatan.update', $kegiatan->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Nama Kegiatan</label>
                            <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan"
                                placeholder="Masukkan nama kegiatan"
                                value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="4" required>{{ old('deskripsi', $kegiatan->deskripsi) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Tanggal Kegiatan</label>
                            <input type="date" class="form-control" name="tanggal_kegiatan"
                                value="{{ old('tanggal_kegiatan', $kegiatan->tanggal_kegiatan->format('Y-m-d')) }}"
                                required>
                        </div>
                        <div class="form-group">
                            <label>Lokasi</label>
                            <input type="text" class="form-control" name="lokasi"
                                value="{{ old('lokasi', $kegiatan->lokasi) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Author</label>
                            <input type="text" class="form-control" name="author"
                                value="{{ old('author', $kegiatan->author) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Gambar Kegiatan</label>
                            <input type="file" class="form-control" name="gambar_kegiatan">
                            @if ($kegiatan->gambar_kegiatan)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $kegiatan->gambar_kegiatan) }}" alt="Gambar Kegiatan"
                                        style="width: 100px; height: 100px; object-fit: cover;">

                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit">Simpan</button>
                            <a href="{{ route('admin.k-kegiatan') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
