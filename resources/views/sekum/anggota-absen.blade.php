
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">ABSEN ANGGOTA</h3> 
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
                                        <h3 class="page-title">{{ $sekumKegiatan->nama_kegiatan }}</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="#" class="btn btn-outline-primary me-2"><i class="fas fa-download"></i> Download</a>
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
                                                <th>Keterangan</th>
                                                <th>Waktu Submit</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sekumAnggotaAbsen as $key=>$list )
                                            <tr>
                                                <input type="text" name="id_absen" value="{{ $list->id_absen }}" hidden>
                                                <input type="text" name="id_kegiatan" value="{{ $list->id_kegiatan }}" hidden>
                                                <input type="text" name="id_anggota" value="{{ $list->id_anggota }}" hidden>
                                                <input type="text" name="poin" value="{{ $list->poin }}" hidden>
                                                <td class="text-center">{{ ++$key }}</td>
                                                <td>{{ $list->nama }}</td>
                                                <td>{{ $list->prodi }}</td>
                                                <td class="text-center">{{ $list->jk }}</td>                                                
                                                <td class="text-center">
                                                    {{ $list->keterangan }}
                                                    <!-- <div>
                                                        <select class="form-control select  @error('keterangan') is-invalid @enderror" name="keterangan[]">>
                                                            <option value="Hadir" {{ $list->keterangan == 'Hadir' ? "selected" :"Hadir"}}>Hadir</option>
                                                            <option value="Izin" {{ $list->keterangan == 'Izin' ? "selected" :"Izin"}}>Izin</option>
                                                            <option value="Alpa" {{ $list->keterangan == 'Alpa' ? "selected" :"Alpa"}}>Alpa</option>
                                                        </select>
                                                        @error('keterangan')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div> -->
                                                </td>
                                                <td>{{ $list->updated_at }}</td>
                                                <td>
                                                    <div class="actions">
                                                        <a href="{{ url('sekum/absen-edit/id_absen='.$list->id_absen) }}" class="btn btn-sm bg-warning text-white">
                                                            <i class="feather-edit"></i>
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
