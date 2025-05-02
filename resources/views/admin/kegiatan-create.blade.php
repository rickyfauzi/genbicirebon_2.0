@extends('layouts.master')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Tambah Kegiatan Baru</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.k-kegiatan') }}">Kegiatan</a></li>
                        <li class="breadcrumb-item active">Tambah Kegiatan</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <form action="{{ route('admin.kegiatan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Nama Kegiatan</label>
                        <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" placeholder="Masukkan nama kegiatan" value="{{ old('nama_kegiatan') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="4" required>{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Kegiatan</label>
                        <input type="date" class="form-control" name="tanggal_kegiatan" value="{{ old('tanggal_kegiatan') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Lokasi</label>
                        <input type="text" class="form-control" name="lokasi" value="{{ old('lokasi') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Author</label>
                        <input type="text" class="form-control" name="author" value="{{ old('author') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Gambar Kegiatan</label>
                        <input type="file" class="form-control" name="gambar_kegiatan" accept="image/*">
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