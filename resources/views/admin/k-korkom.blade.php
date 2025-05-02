@extends('layouts.master')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h3 class="page-title">Manajemen Korkom GenBI Cirebon</h3>
                        <ul class="breadcrumb">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span><i class="fas fa-user-tie"></i> Korkom</span>
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
            <div class="col-sm-12">
                <div class="card card-table shadow">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="page-title">Daftar Korkom GenBI Cirebon</h3>
                                </div>
                                <div class="col-auto text-end float-end ms-auto download-grp">
                                    <a href="{{ route('admin.korkom-create') }}" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-plus"></i> Tambah Korkom
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @foreach ($korkoms as $korkom)
                            <div class="col-md-4 mb-2 mt-2 pb-2">
                                <div class="card mb-6 pb-5">
                                    @if ($korkom->gambar)
                                    <img src="{{ asset('storage/' . $korkom->gambar) }}" alt="{{ $korkom->nama }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                    @else
                                    <img src="{{ asset('images/default-image.png') }}" alt="Default Image" class="card-img-top" style="height: 200px; object-fit: cover;">
                                    @endif
                                    <div class="card-body shadow" style="border-radius: 20px;">
                                        <h5 class="card-title">{{ $korkom->nama }}</h5>
                                        <p class="card-text"><strong>Jabatan: </strong>{{ $korkom->jabatan }}</p>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('admin.korkom-edit', $korkom->id) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('admin.korkom.delete', $korkom->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus korkom ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection