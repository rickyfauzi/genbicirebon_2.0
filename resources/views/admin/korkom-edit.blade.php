@extends('layouts.master')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Edit Korkom</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item active">Edit Korkom</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.korkom.update', $korkom->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="nama">Nama Korkom</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama', $korkom->nama) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="jabatan">Jabatan</label>
                                <input type="text" class="form-control" id="jabatan" name="jabatan" value="{{ old('jabatan', $korkom->jabatan) }}" required>
                            </div>
                            <div class="form-group">
                                <label for="gambar">Gambar</label>
                                <input type="file" class="form-control" id="gambar" name="gambar">
                                @if ($korkom->gambar)
                                <img src="{{ asset('storage/' . $korkom->gambar) }}" alt="{{ $korkom->nama }}" class="img-thumbnail mt-2" style="max-width: 200px;">
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection