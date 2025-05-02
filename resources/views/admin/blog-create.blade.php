@extends('layouts.master')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h3 class="page-title">Tambah Blog GenBI Cirebon</h3>
                        <ul class="breadcrumb">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span><i class="fas fa-newspaper"></i> Blog</span>
                                </div>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow">
                    <div class="card-body">
                        <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="nama_blog">Judul Blog</label>
                                <input type="text" name="nama_blog" class="form-control" id="nama_blog" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_blog">Tanggal Blog</label>
                                <input type="date" name="tanggal_blog" class="form-control" id="tanggal_blog" required>
                            </div>
                            <div class="form-group">
                                <label for="deskripsi1">Deskripsi 1</label>
                                <textarea name="deskripsi1" class="form-control" id="deskripsi1" rows="4" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="deskripsi2">Deskripsi 2</label>
                                <textarea name="deskripsi2" class="form-control" id="deskripsi2" rows="4"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="author">Author</label>
                                <input type="text" name="author" class="form-control" id="author" required>
                            </div>
                            <div class="form-group">
                                <label for="gambar">Gambar</label>
                                <input type="file" name="gambar" class="form-control" id="gambar">
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Blog</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection