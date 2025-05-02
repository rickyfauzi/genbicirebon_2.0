
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">ABSEN KEGIATAN</h3>
                            <ul class="breadcrumb">
                                <div class="row align-items-center">
                                    <div class="col">
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">                             
                                        <a href="{{ route('sekum/kegiatan-add') }}" class="btn btn-primary text-white"><i class="fas fa-plus"></i>&nbsp; Tambah Kegiatan</a>
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            {{-- message --}}
            {!! Toastr::message() !!} 
            @if (Route::is('sekum/kegiatan/genbi'))
            <!-- ABSEN KEGIATAN GENBI -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table comman-shadow">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Kegiatan GenBI Cirebon</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="#" class="btn btn-outline-primary me-2"><i class="fas fa-download"></i> Download</a>                                        
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm" id="dtBasicExample">
                                    <thead class="student-thread text-center">
                                        <tr class="text-center">
                                            <th hidden>id</th>
                                            <th>No</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Tanggal Pelaksanaan</th>
                                            <th>Poin</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sekumKegiatanGenbi as $key=>$list)
                                        <tr>
                                            <td hidden class="id">{{ $list->id_kegiatan }}</td> 
                                            <p hidden>{{ $list->id_komisariat }}</p>
                                            <td class="text-center">{{ ++$key }}</td>                                        
                                            <td>{{ $list->nama_kegiatan }}</td>
                                            <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                            <td class="text-center">{{ $list->poin }}</td>
                                            <td>
                                                <div class="actions">
                                                    <a href="{{ url('sekum/anggota-absen/id_kegiatan='.$list->id_kegiatan.'&&id_komisariat='.$list->id_komisariat) }}" class="btn btn-sm bg-success">
                                                        <i class="feather-users"></i>
                                                    </a>
                                                </div>
                                            </td>                                            
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <!-- END ABSEN KEGIATAN GENBI -->
            
            @elseif (Route::is('sekum/kegiatan/utama'))
            <!-- ABSEN KEGIATAN UTAMA -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table comman-shadow">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Kegiatan Utama</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="#" class="btn btn-outline-primary me-2"><i class="fas fa-download"></i> Download</a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm" id="dtBasicExample">
                                    <thead class="student-thread text-center">
                                        <tr class="text-center">
                                            <th hidden>id</th>
                                            <th>No</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Tanggal Pelaksanaan</th>
                                            <th>Poin</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sekumKegiatanUtama as $key=>$list)
                                        <tr>
                                            <td hidden class="id">{{ $list->id_kegiatan }}</td> 
                                            <p hidden>{{ $list->id_komisariat }}</p>
                                            <td class="text-center">{{ ++$key }}</td>     
                                            <td>{{ $list->nama_kegiatan }}</td>
                                            <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                            <td class="text-center">{{ $list->poin }}</td>
                                            <td>
                                                <div class="actions">
                                                    <a href="{{ url('sekum/anggota-absen/id_kegiatan='.$list->id_kegiatan.'&&id_komisariat='.$list->id_komisariat) }}" class="btn btn-sm bg-success">
                                                        <i class="feather-users"></i>
                                                    </a>
                                                    <p>&ensp;</p>
                                                    <a href="{{ url('sekum/kegiatan-edit/'.$list->id_kegiatan) }}" class="btn btn-sm bg-warning">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <p>&ensp;</p>
                                                    <a class="btn btn-sm bg-danger kegiatan_delete" data-bs-toggle="modal" data-bs-target="#studentUser">
                                                        <i class="feather-trash-2 me-1"></i>
                                                    </a>
                                                </div>
                                            </td>                                            
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
            <!-- END ABSEN KEGIATAN UTAMA -->
            
            @elseif (Route::is('sekum/kegiatan/tambahan'))
            <!-- ABSEN KEGIATAN TAMBAHAN -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table comman-shadow">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Kegiatan Tambahan</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="#" class="btn btn-outline-primary me-2"><i class="fas fa-download"></i> Download</a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm" id="dtBasicExample">
                                    <thead class="student-thread text-center">
                                        <tr class="text-center">
                                            <th hidden>id</th>
                                            <th>No</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Tanggal Pelaksanaan</th>
                                            <th>Poin</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sekumKegiatanTambahan as $key=>$list)
                                        <tr>
                                            <td hidden class="id">{{ $list->id_kegiatan }}</td> 
                                            <p hidden>{{ $list->id_komisariat }}</p>
                                            <td class="text-center">{{ ++$key }}</td>     
                                            <td>{{ $list->nama_kegiatan }}</td>
                                            <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                            <td class="text-center">{{ $list->poin }}</td>
                                            <td>
                                                <div class="actions">
                                                    <a href="{{ url('sekum/anggota-absen/id_kegiatan='.$list->id_kegiatan.'&&id_komisariat='.$list->id_komisariat) }}" class="btn btn-sm bg-success">
                                                        <i class="feather-users"></i>
                                                    </a>
                                                    <p>&ensp;</p>
                                                    <a href="{{ url('sekum/kegiatan-edit/'.$list->id_kegiatan) }}" class="btn btn-sm bg-warning">
                                                        <i class="feather-edit"></i>
                                                    </a>
                                                    <p>&ensp;</p>
                                                    <a class="btn btn-sm bg-danger kegiatan_delete" data-bs-toggle="modal" data-bs-target="#studentUser">
                                                        <i class="feather-trash-2 me-1"></i>
                                                    </a>
                                                </div>
                                            </td>                                            
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
            <!-- END ABSEN KEGIATAN TAMBAHAN -->
            @endif
        </div>
    </div>
    {{-- model student delete --}}
    <div class="modal fade contentmodal" id="studentUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content doctor-profile">
                <div class="modal-header pb-0 border-bottom-0  justify-content-end">
                    <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close"><i
                        class="feather-x-circle"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('sekum/delete') }}" method="POST">
                        @csrf
                        <div class="delete-wrap text-center">
                            <div class="del-icon">
                                <i class="feather-x-circle"></i>
                            </div>
                            <input type="hidden" name="id_kegiatan" class="e_id" value="">
                            <h2>Yakin ingin menghapus data?</h2>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-success me-2">Yes</button>
                                <a class="btn btn-danger" data-bs-dismiss="modal">No</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @section('script')

    {{-- delete js --}}
    <script>
        $(document).on('click','.kegiatan_delete',function()
        {
            var _this = $(this).parents('tr');
            $('.e_id').val(_this.find('.id').text());
        });

        $(document).ready(function () {
            $('#dtBasicExample').DataTable();
            $('.dataTables_length').addClass('bs-select');
        });
    </script>
    @endsection

@endsection
