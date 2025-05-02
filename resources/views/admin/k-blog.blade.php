@extends('layouts.master')

@section('content')
<style>
    .table-sm th,
    .table-sm td {
        font-size: 12px; /* Ubah ukuran font */
        padding: 4px 8px; /* Perkecil padding */
    }
    .table-sm th {
        font-weight: bold;
    }
    .table-sm img {
        border-radius: 4px;
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">

        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h4 class="page-title">Manajemen Blog GenBI Cirebon</h4>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><i class="fas fa-newspaper me-1"></i> Blog</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {!! Toastr::message() !!}

        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table shadow">
                    <div class="card-body">

                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="page-title">Daftar Blog</h5>
                                </div>
                                <div class="col-auto text-end float-end ms-auto">
                                    <a href="{{ route('admin.blog-create') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Tambah Blog
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 10%;">Gambar</th>
                                        <th style="width: 20%;">Judul</th>
                                        <th style="width: 12%;">Tanggal</th>
                                        <th style="width: 25%;">Deskripsi</th>
                                        <th style="width: 16%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($blogs as $index => $blog)
                                    <tr>
                                        <td>{{ $index + $blogs->firstItem() }}</td>
                                        <td>
                                            <img src="{{ $blog->gambar ? asset('storage/' . $blog->gambar) : asset('images/default-image.png') }}" width="60" height="40" style="object-fit: cover;">
                                        </td>
                                        <td>{{ \Illuminate\Support\Str::limit($blog->nama_blog, 35) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($blog->tanggal_blog)->format('d M Y') }}</td>
                                       
                                        <td class="text-start">{{ \Illuminate\Support\Str::limit(strip_tags($blog->deskripsi1), 50) }}</td>
                                        <td>
                                            <a href="{{ route('admin.blog-edit', $blog->id) }}" class="btn btn-sm btn-warning mb-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.blog.delete', $blog->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus blog ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7">Belum ada data blog.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                       <nav aria-label="Page navigation example" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    {{-- Previous --}}
                                    @if ($blogs->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link"
                                                href="{{ $blogs->previousPageUrl() }}">Sebelumnya</a></li>
                                    @endif

                                    {{-- Page numbers --}}
                                    @for ($i = 1; $i <= $blogs->lastPage(); $i++)
                                        <li class="page-item {{ $blogs->currentPage() == $i ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $blogs->url($i) }}">{{ $i }}</a>
                                        </li>
                                    @endfor

                                    {{-- Next --}}
                                    @if ($blogs->hasMorePages())
                                        <li class="page-item"><a class="page-link"
                                                href="{{ $blogs->nextPageUrl() }}">Berikutnya</a></li>
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
