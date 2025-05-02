@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Manegement Kegiatan GenBI Cirebon</h3>
                            <ul class="breadcrumb">
                                <div class="row align-items-center">
                                    <div class="col">
                                        {{-- <span>Kegiatan</span> --}}
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
                                    
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm"
                                    id="dtBasicExample">
                                    <thead class="student-thread text-center">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Tanggal Pelaksanaan</th>
                                            <th>Komisariat</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($adminKegiatan as $key => $list)
                                            <tr>
                                                <td class="text-center">{{ ++$key }}</td>
                                                <td>{{ $list->nama_kegiatan }}</td>
                                                <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                                <td>{{ $list->komisariat }}</td>

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
    <!-- END ABSEN KEGIATAN UTAMA -->


    <script>
        $(document).ready(function() {
            $('#dtBasicExample').DataTable();
            $('.dataTables_length').addClass('bs-select');
        });
    </script>
@endsection
