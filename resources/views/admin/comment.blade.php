@extends('layouts.master')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h3 class="page-title">Manajemen Komentar GenBI Cirebon</h3>
                        <ul class="breadcrumb">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span><i class="fas fa-building"></i> Komentar</span>
                                </div>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Message --}}
        {!! Toastr::message() !!}

        {{-- Daftar Komentar --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table shadow">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="page-title">Komentar Berita</h3>
                                </div>
                            </div>
                        </div>

                        {{-- Tabel Daftar Komentar --}}
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama</th>
                                        <th>Komentar</th>
                                        <th>Berita</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($comments as $comment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $comment->user->name }}</td>
                                            <td>{{ Str::limit($comment->komentar, 50) }}</td>
                                           <td style="white-space: normal; word-wrap: break-word;">
    {{ $comment->blog->nama_blog ?? 'Tidak Ditemukan' }}
</td>

                                            <td>
                                                <span class="badge 
                                                    {{ $comment->status === 'approved' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ucfirst($comment->status) }}
                                                </span>
                                            </td>
                                          <td>
                                            {{-- Tombol Aksi --}}
                                           <!-- Tombol Aksi -->
<div class="btn-group">
    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Aksi
    </button>
    <ul class="dropdown-menu">
        <!-- Tombol Setujui -->
        @if ($comment->status != 'approved' && $comment->status != 'rejected')
            <form action="{{ route('admin.comments.approve', $comment->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item btn-success bg-success text-white">
                    <i class="fas fa-check"></i> Setujui
                </button>
            </form>
        @endif

        <!-- Tombol Tolak -->
        @if ($comment->status != 'approved' && $comment->status != 'rejected')
            <form action="{{ route('admin.comments.reject', $comment->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item btn-danger bg-danger text-white">
                    <i class="fas fa-times"></i> Tolak
                </button>
            </form>
        @endif
    </ul>
</div>

                                            
                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('admin.comments.delete', $comment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada komentar yang tersedia.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript untuk menangani dropdown toggle di halaman ini
$(document).ready(function () {
    // Mengaktifkan dropdown pada klik tombol
    $('.dropdown-toggle').click(function () {
        var $this = $(this);
        var $dropdownMenu = $this.next('.dropdown-menu');
        
        // Toggle dropdown menu
        $dropdownMenu.toggleClass('show');
        
        // Close dropdown jika diklik di luar
        $(document).click(function (event) {
            if (!$(event.target).closest('.dropdown-toggle, .dropdown-menu').length) {
                $dropdownMenu.removeClass('show');
            }
        });
    });
});

</script>

@endsection
