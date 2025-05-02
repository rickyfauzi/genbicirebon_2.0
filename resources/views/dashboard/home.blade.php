@extends('layouts.master')
@section('content')
{{-- message --}}
{!! Toastr::message() !!}
<div class="page-wrapper">
    <div class="content container-fluid">
        @if (Session::get('role_name') === 'Anggota' || Session::get('role_name') === 'Anggota')
        <div class="row">
            <h6 class="text-center">"{{ $kataMutiara->judul }}" - {{ $kataMutiara->pengarang }}</h6>
            <br><br>
            <div class="col-sm-9">
                <div class="card card-table shadow">
                    <div class="card-body">
                        <h2 class="">Halo {{ Session::get('name') }}!</h2>
                        <h6 class="page-title">Komisariat {{ $komisariat->komisariat }}</h6>
                        <div class='line'></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card bg-info mb-3 card-table shadow">
                    <div class="card-body">
                        <h6 class="page-title text-white">Poin Kamu</h6>
                        <h1 class="text-white"># {{ $poin }}</h1>
                    </div>
                </div>
            </div>
        </div>
        <!-- ABSEN KEGIATAN HARI INI -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table shadow">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="page-title">Kegiatan Hari Ini</h3>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm"
                                id="dtBasicExample">
                                <thead class="student-thread text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Kegiatan</th>
                                        <th>Tanggal Pelaksanaan</th>
                                        <th>Jenis</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kegiatanNow as $key => $list)
                                    <tr>
                                        <p hidden class="id">{{ $list->id }}</p>
                                        <p hidden class="id">{{ $list->id_komisariat }}</p>
                                        <td class="text-center">{{ ++$key }}</td>
                                        <td>{{ $list->nama_kegiatan }}</td>
                                        <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                        <td class="text-center">{{ $list->jenis }}</td>
                                        <td>
                                            <div class="actions">
                                                <a href="{{ url('anggota/absen-anggota/id_kegiatan=' . $list->id_kegiatan) }}"
                                                    class="btn btn-sm bg-success text-white">
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
        <!-- END ABSEN KEGIATAN HARI INI -->
        @endif

        @if (Session::get('role_name') === 'Sekretaris Umum' || Session::get('role_name') === 'Sekretaris Umum')
        <div class="row">
            <h6 class="text-center">"{{ $kataMutiara->judul }}" - {{ $kataMutiara->pengarang }}</h6>
            <br><br>
            <div class="col-sm-12">
                <div class="card card-table comman-shadow">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <h2 class="">Halo, {{ Session::get('name') }}!</h2>
                                    <h6 class="page-title">Komisariat {{ $komisariat->komisariat }}</h6>
                                    <div class='line'></div>
                                </div>
                                <div class="col-sm-6">
                                    <img src="{{ URL::to('assets/img/dashboard-img-2.png') }}" class="w-50"
                                        style="float: right;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ABSEN KEGIATAN HARI INI -->
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-table shadow">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="page-title">Kegiatan Hari Ini</h3>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm"
                                id="dtBasicExample">
                                <thead class="student-thread text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Kegiatan</th>
                                        <th>Tanggal Pelaksanaan</th>
                                        <th>Jenis</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($kegiatanNow as $key => $list)
                                    <tr>
                                        <p hidden class="id">{{ $list->id }}</p>
                                        <p hidden class="id">{{ $list->id_komisariat }}</p>
                                        <td class="text-center">{{ ++$key }}</td>
                                        <td>{{ $list->nama_kegiatan }}</td>
                                        <td>{{ date('l, d F Y', strtotime($list['tgl_pelaksanaan'])) }}</td>
                                        <td class="text-center">{{ $list->jenis }}</td>
                                        <td>
                                            <div class="actions">
                                                <a href="{{ url('sekum/anggota-absen/id_kegiatan=' . $list->id_kegiatan . '&&id_komisariat=' . $list->id_komisariat) }}"
                                                    class="btn btn-sm bg-success text-white">
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
        <!-- END ABSEN KEGIATAN HARI INI -->

        {{-- RANKING POIN --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="row align-items-center">
                                <div class="col">
                                    <h3 class="page-title">Peringkat Kehadiran</h3>
                                </div>
                            </div>
                            <br>
                                <table class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm">
                                    <thead class="student-thread">
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Prodi</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Poin</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($topAnggota as $key=>$list )
                                        <tr>
                                            <p hidden>{{ $list->id_kegiatan }}</p>
                                            <p hidden>{{ $list->id_anggota }}</p>
                                            <td class="text-center">{{ ++$key }}</td>
                                            <td>{{ $list->nama }}</td>
                                            <td>{{ $list->prodi }}</td>
                                            <td class="text-center">{{ $list->jk }}</td>
                                            <td class="text-center">{{ $list->total_poin }}</td>
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
        @endif

        @if (Session::get('role_name') === 'Pembina' || Session::get('role_name') === 'Pembina')
        <div class="row">
            <h6 class="text-center">"{{ $kataMutiara->judul }}" - {{ $kataMutiara->pengarang }}</h6>
            <br><br>
            <div class="col-sm-12">
                <div class="card card-table comman-shadow">
                    <div class="card-body">
                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <h2 class="">Halo <b>{{ Session::get('name') }}</b>!</h2>
                                    <h6 class="page-title">Pembina GenBI Cirebon</h6>
                                    <h6 class="page-title">Selamat Datang di SATU GenBI</h6>
                                    <div class='line'></div>
                                </div>
                                <div class="col-sm-6">
                                    <img src="{{ URL::to('assets/img/dashboard-img-1.png') }}" class="w-50"
                                        style="float: right;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if (Session::get('role_name') === 'Admin' || Session::get('role_name') === 'Admin')
        <div class="col-sm-12">
            <h6 class="text-center">"{{ $kataMutiara->judul }}" - {{ $kataMutiara->pengarang }}</h6>
            <div class="card card-table comman-shadow">
                <div class="card-body">
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col-sm-6">
                                <h2 class="">Halo, {{ Session::get('name') }}!</h2>
                                <h6 class="page-title">Komisariat {{ $komisariat->komisariat }}</h6>
                                <div class='line'></div>
                            </div>
                            <div class="col-sm-6">
                                <img src="{{ URL::to('assets/img/dashboard-img-2.png') }}" class="w-50"
                                    style="float: right;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@section('script')
<script>
    $(document).ready(function() {
        $('#dtBasicExample').DataTable();
        $('.dataTables_length').addClass('bs-select');
    });
</script>

<!-- Script untuk mendapatkan dan menampilkan kata mutiara secara acak -->
<script>
    function getRandomKataMutiara() {
        fetch('/home')
            .then(response => response.json())
            .then(data => {
                document.getElementById('kataMutiara').innerHTML = data.judul;
            }).catch(error => console.error('Error:', error));
    }

    // Tampilkan kata mutiara pertama kali
    getRandomKataMutiara();

    // Event listener untuk reload halaman
    window.addEventListener('beforeunload', function(event) {
        // Tampilkan kata mutiara baru setiap kali halaman direload
        getRandomKataMutiara();
    });
</script>
@endsection

@endsection