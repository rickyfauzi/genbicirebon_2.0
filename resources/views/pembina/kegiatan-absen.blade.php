
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
                                </div>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            {{-- message --}}
            {!! Toastr::message() !!}            
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
                                    class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread text-center">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Tanggal Pelaksanaan</th>
                                            <th>Poin</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pembinaKegiatanUtama as $key=>$list)
                                        <tr>
                                            <td class="text-center">{{ ++$key }}</td>
                                            <td hidden class="id">{{ $list->id }}</td>
                                            <td hidden class="id">{{ $list->id_komisariat }}</td>
                                            <td>{{ $list->nama_kegiatan }}</td> 
                                            <td class="text-center">{{ date('l, d-m-Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                            <td class="text-center">{{ $list->poin }}</td>
                                            <td>
                                                <div class="actions">
                                                    <a href="{{ url('pembina/absen-anggota/id='.$list->id.'&&id_komisariat='.$list->id_komisariat) }}" class="btn btn-sm bg-success">
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
            <!-- END ABSEN KEGIATAN UTAMA -->

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
                                    class="table border-0 star-student table-hover table-center mb-0 datatable table-striped">
                                    <thead class="student-thread text-center">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Tanggal Pelaksanaan</th>
                                            <th>Poin</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pembinaKegiatanTambahan as $key=>$list)
                                        <tr>
                                            <td class="text-center">{{ ++$key }}</td>
                                            <td hidden class="id">{{ $list->id }}</td>
                                            <td hidden class="id">{{ $list->id_komisariat }}</td>
                                            <td>{{ $list->nama_kegiatan }}</td> 
                                            <td class="text-center">{{ date('l, d-m-Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                            <td class="text-center">{{ $list->poin }}</td>
                                            <td>
                                                <div class="actions">
                                                    <a href="{{ url('pem/absen-anggota/id='.$list->id.'&&id_komisariat='.$list->id_komisariat) }}" class="btn btn-sm bg-success">
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
            <!-- END ABSEN KEGIATAN TAMBAHAN -->
        </div>
    </div>

@endsection
