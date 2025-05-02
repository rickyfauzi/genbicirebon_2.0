
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">ANGGOTA</h3> 
                        </div>
                    </div>
                </div>
            </div>

            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card comman-shadow">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">GenBI Cirebon</h3>
                                    </div>
                                </div>
                                <br>
                                    <table class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm" id="dtBasicExample">
                                        <thead class="student-thread">
                                            <tr class="text-center">
                                                <th>No</th>
                                                <th>Nama</th>
                                                <th>Prodi</th>
                                                <th>Jenis Kelamin</th>
                                                <th>Komisariat</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pembinaAnggota as $key=>$list )
                                            <tr>
                                                <td class="text-center">{{ ++$key }}</td>
                                                <td>{{ $list->nama }}</td>
                                                <td>{{ $list->prodi }}</td>
                                                <td class="text-center">{{ $list->jk }}</td>
                                                <td>{{ $list->komisariat }}</td>
                                                <td>{{ $list->status }}</td>
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
