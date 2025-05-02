@extends('layouts.master')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <div class="page-sub-header">
                        <h3 class="page-title">Manajemen Komisariat GenBI Cirebon</h3>
                        <ul class="breadcrumb">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span><i class="fas fa-building"></i> Komisariat</span>
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
                                    <h3 class="page-title">Daftar Komisariat GenBI Cirebon</h3>
                                </div>
                                <div class="col-auto text-end float-end ms-auto download-grp">
                                    <button class="btn btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#modalTambahKomisariat">
                                        <i class="fas fa-plus"></i> Tambah Komisariat
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @foreach ($komisariats as $komisariat)
                            <div class="col-md-4 mb-2 mt-2 pb-2 d-flex justify-content-center">
                                <div class="card mb-2 pb-1 d-flex flex-column align-items-center">
                                    @if ($komisariat->image)
                                    <img src="{{ asset('storage/' . $komisariat->image) }}" alt="{{ $komisariat->komisariat }}" class="card-img-top" style="height:120px; width:120px;">
                                    @else
                                    <img src="{{ asset('images/default-image.png') }}" alt="Default Image" class="card-img-top">
                                    @endif
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center" style="border-radius: 20px;">
                                        <h5 class="card-title text-center">{{ $komisariat->komisariat }}</h5>
                                        <div class="d-flex justify-content-center mt-3">
                                            <a href="{{ route('admin.komis-edit', $komisariat->id_komisariat) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                        <div class="d-flex justify-content-center mt-3">
                                            <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $komisariat->id_komisariat }}">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
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

<!-- Modal Tambah Komisariat -->
<div class="modal fade" id="modalTambahKomisariat" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Tambah Komisariat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahKomisariat">
                    @csrf
                    <div class="mb-3">
                        <label for="komisariat" class="form-label">Nama Komisariat</label>
                        <input type="text" class="form-control" id="komisariat" name="komisariat" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Gambar</label>
                        <input type="file" class="form-control" id="image" name="image">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Hapus Komisariat dengan AJAX
    $(document).on('click', '.btn-delete', function () {
        let id = $(this).data('id');
        let url = `/admin/komisariat/${id}/delete`;

        if (confirm('Apakah Anda yakin ingin menghapus komisariat ini?')) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    alert('Data berhasil dihapus');
                    location.reload();
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat menghapus data');
                }
            });
        }
    });

    // Tambah Komisariat dengan AJAX
    $('#formTambahKomisariat').on('submit', function (e) {
        e.preventDefault();

        let formData = new FormData(this);
        $.ajax({
            url: '/admin/komis/store',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                alert('Data berhasil ditambahkan');
                $('#modalTambahKomisariat').modal('hide');
                location.reload();
            },
            error: function (xhr) {
                alert('Terjadi kesalahan saat menambahkan data');
            }
        });
    });
</script>
@endpush
