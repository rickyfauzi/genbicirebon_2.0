@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-sm-12 md-5 ">
                    <div class="card comman-shadow">
                        <div class="card-body">
                            <form action="{{ route('admin/galery/tambah') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <h5 class="form-title student-info">Tambah Galery Kegiatan </h5>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Judul Kegiatan<span class="login-danger">*</span></label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title"
                                                placeholder="Masukan Judul Kegiatan" ">
                                                @error('title')
                                                <span class="invalid-feedback" role="alert">
                                                 <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            </div>
                                        </div>
                                    

                                            
                                         <div class="col-12 col-sm-4">
                                            <div class="form-group local-forms">
                                                <label for="image_path">Masukan Photo Kegiatan<span class="login-danger">*</span></label>
                                                <input   class="form-control" type="file" name="image_path" id="image_path" accept="image_path/*">
                                            @error('image_path')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                               
                                    <div class="col-12 col-sm-4">
                                        <div class="form-group local-forms">
                                            <label>Masukan Author<span class="login-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('author') is-invalid @enderror"
                                                name="author" placeholder="Masukan author" ">
                                                @error('author')
                                                <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                </span>
                                                 @enderror
                                            </div>
                                        </div>
                                        <div class="col-12 ">
                                            <div class="form-group">
                                                <label>Deksripsi Kegiatan<span class="login-danger">*</span></label>
                                                <textarea name="description" id="description" rows="10" cols="80"  placeholder="Enter your article content here...">

                                                </textarea>
                                                @error('description')
                                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $message }}</strong>
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="student-submit">
                                                <button type="submit" class="btn btn-success" ><i class="fas fa-save"></i> Simpan</button>
                                                <a href="{{ route('admin/galery') }}" class="btn btn-danger"><i class="fas fa-backward"></i> Kembali</a>
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
    
        <script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
        <script>
            ClassicEditor
                .create(document.querySelector('#description'))
                .catch(error => {
                    console.error(error);
                });
        </script>
@endsection
