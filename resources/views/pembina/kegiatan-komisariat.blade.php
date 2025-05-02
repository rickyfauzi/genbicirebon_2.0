
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">REKAP ABSEN</h3>
                            <ul class="breadcrumb">
                                <!-- <li class="breadcrumb-item active">Absen Kegiatan</li> -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SELECT KEGIATAN -->
            <div class="col-12 col-sm-4">
                <form action="rekap-absen-result" method="GET" id="form_id">
                    <div class="form-group">
                        <select class="form-control select  @error('kegiatan') is-invalid @enderror" name="id_kegiatan" onChange="document.getElementById('form_id').submit();">
                            <option selected disabled>Pilih Kegiatan</option>
                            @foreach ($pembinaKegiatan as $key=>$list)
                            <option value="{{ $list->id_kegiatan }}">{{ $list->jenis }} - {{ $list->nama_kegiatan }}</option>   
                            @endforeach
                        </select>
                        @error('kegiatan')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </form>
            </div>  
            <!-- END SELECT KEGIATAN -->
            {{-- message --}}
            {!! Toastr::message() !!}            
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table comman-shadow">
                        <div class="card-body">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Absen Rekap</h3>
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
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Prodi</th>
                                            <th>Jenis Kelamin</th> 
                                            <th>Kehadiran</th>
                                            <th>Poin</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($pembinaAbsen as $key=>$list )
                                        <tr>
                                            <td class="text-center">{{ ++$key }}</td>
                                            <td>{{ $list->nama }}</td>
                                            <td>{{ $list->prodi }}</td>
                                            <td class="text-center">{{ $list->jk }}</td>
                                            <td class="text-center">Hadir</td>
                                            <td class="text-center">100</td>
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
    @section('script')

    {{-- delete js --}}
    <script>
        $(document).ready(function () {
            $('#dtBasicExample').DataTable();
            $('.dataTables_length').addClass('bs-select');
        });
    </script>
    @endsection

@endsection
