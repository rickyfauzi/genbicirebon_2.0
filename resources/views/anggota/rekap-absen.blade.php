@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">RIWAYAT ABSEN</h3> 
                        </div>
                    </div>
                </div>
            </div> -->

            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="row align-items-center">
                                        <h3 class="page-title">Riwayat Absensi</h3>
                                    </div>
                                </div>
                                <br><br>
                                <div class="table-responsive">
                                    <table class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm" id="dtBasicExample">
                                        <thead class="student-thread">
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>Nama Kegiatan</th>
                                                <th>Tanggal Pelaksanaan</th>
                                                <th>Jenis</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($anggotaRekap as $key=>$list)
                                            <tr>
                                                <td class="text-center">{{ ++$key }}</td>
                                                <td>{{ $list->nama_kegiatan }}</td>
                                                <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                                <td>{{ $list->jenis }}</td>
                                                <td>{{ $list->keterangan }}</td>
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
    </div>

    @section('script')
    <script>
        $(document).ready(function () {
            $('#dtBasicExample').DataTable();
            $('.dataTables_length').addClass('bs-select');
        });
    </script>
    @endsection

@endsection
