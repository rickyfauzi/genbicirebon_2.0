
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">REKAP KEGIATAN</h3>
                            <ul class="breadcrumb">
                                <div class="row align-items-center">
                                    <div class="col">
                                    </div>
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            {{-- message --}}
            {!! Toastr::message() !!} 
            <!-- ABSEN KEGIATAN GENBI -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table shadow">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">{{ $pembinaKeg->komisariat }}</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('export/kegiatan')}}" class="btn btn-outline-primary me-2"><i class="fas fa-download"></i> Download</a>  
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
                                            <th>Jenis Kegiatan</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pembinaKegiatan as $key=>$list)
                                        <tr>
                                            <td hidden class="id">{{ $list->id_kegiatan }}</td> 
                                            <p hidden>{{ $list->id_komisariat }}</p>
                                            <td class="text-center">{{ ++$key }}</td>          
                                            <td>{{ $list->nama_kegiatan }}</td>
                                            <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                            <td>{{ $list->jenis }}</td>
                                            <td>
                                                <div class="actions">
                                                    <a href="{{ url('pembina/rekap-absen/id_kegiatan='.$list->id_kegiatan) }}" class="btn btn-sm bg-success text-white">
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
        </div>
    </div>
    
    <script>
        $(document).ready(function () {
            $('#dtBasicExample').DataTable();
            $('.dataTables_length').addClass('bs-select');
        });
    </script>

@endsection
