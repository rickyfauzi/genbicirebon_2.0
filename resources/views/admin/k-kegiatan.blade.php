<style>
    .table-sm th,
    .table-sm td {
        font-size: 12px;
        /* Ubah ukuran font */
        padding: 4px 8px;
        /* Perkecil padding */
    }

    .table-sm th {
        font-weight: bold;
    }

    .table-sm img {
        border-radius: 4px;
    }
</style>

@extends('layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">

            {{-- Header --}}
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Manajemen Kegiatan GenBI Cirebon</h3>
                            <ul class="breadcrumb">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <span><i class="fas fa-tasks me-1"></i> Kegiatan</span>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Flash Message --}}
            {!! Toastr::message() !!}

            {{-- Table Content --}}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table shadow-sm">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="page-title">Daftar Kegiatan</h4>
                                    </div>
                                    <div class="col-auto text-end ms-auto">
                                        <a href="{{ route('admin.kegiatan-create') }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Tambah Kegiatan
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-sm table-bordered table-hover align-middle text-center">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Gambar</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Tanggal</th>
                                            <th>Lokasi</th>
                                            <th style="width: 150px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($kegiatann as $index => $kegiatan)
                                            <tr>
                                                <td>{{ $kegiatann->firstItem() + $index }}</td>
                                                <td>
                                                    <img src="{{ $kegiatan->gambar_kegiatan ? asset('storage/' . $kegiatan->gambar_kegiatan) : asset('images/default-image.png') }}"
                                                        alt="Gambar" width="60" height="40"
                                                        style="object-fit: cover; border-radius: 4px;">
                                                </td>
                                                <td class="text-start">
                                                    {{ \Illuminate\Support\Str::limit($kegiatan->nama_kegiatan, 50) }}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($kegiatan->tanggal_kegiatan)->format('d M Y') }}
                                                </td>
                                                <td class="text-start">{{ $kegiatan->lokasi }}</td>
                                                <td>
                                                    <a href="{{ route('admin.kegiatan.edit', $kegiatan->id) }}"
                                                        class="btn btn-sm btn-warning mb-1">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.kegiatan.delete', $kegiatan->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Anda yakin ingin menghapus kegiatan ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Tetap pakai pagination kamu --}}
                            <nav aria-label="Page navigation example" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    {{-- Previous --}}
                                    @if ($kegiatann->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link"
                                                href="{{ $kegiatann->previousPageUrl() }}">Sebelumnya</a></li>
                                    @endif

                                    {{-- Page Numbers --}}
                                    @for ($i = 1; $i <= $kegiatann->lastPage(); $i++)
                                        <li class="page-item {{ $kegiatann->currentPage() == $i ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $kegiatann->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    {{-- Next --}}
                                    @if ($kegiatann->hasMorePages())
                                        <li class="page-item"><a class="page-link"
                                                href="{{ $kegiatann->nextPageUrl() }}">Berikutnya</a></li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">Berikutnya</span></li>
                                    @endif
                                </ul>
                            </nav>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
