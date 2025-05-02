
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- <div class="page-header">
                <div class="row align-items-center">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Title</h3> 
                        </div>
                    </div>
                </div>
            </div> -->
            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="row">
                <div class="col-sm-12">
                    <div class="card comman-shadow">
                        <div class="card-body">
                            <div class="">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">{{ $sekumAbsenEdit->nama_kegiatan }}</h3>
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('sekum/absen/update') }}" method="POST" enctype="multipart/form-data">
                                @csrf      
                                <table>
                                    <tr>
                                        <td>Nama</td>
                                        <td>&emsp; : &emsp;</td>
                                        <td>{{ $sekumAbsenEdit->nama }}</td>
                                    </tr>
                                    <tr>
                                        <td>Prodi</td>
                                        <td>&emsp; : &emsp;</td>
                                        <td>{{ $sekumAbsenEdit->prodi }}</td>
                                    </tr>
                                    <tr>
                                        <td>Jenis Kelamin</td>
                                        <td>&emsp; : &emsp;</td>
                                        <td>{{ $sekumAbsenEdit->jk }}</td>
                                    </tr>
                                    <tr>
                                        <td>Waktu Submit</td>
                                        <td>&emsp; : &emsp;</td>
                                        <td>{{ $sekumAbsenEdit->updated_at }}</td>
                                    </tr>
                                    <tr>
                                        <td>Keterangan Presensi</td>
                                        <td>&emsp; : &emsp;</td>
                                        <td>
                                            <select class="form-control select  @error('keterangan') is-invalid @enderror" name="keterangan">
                                                <option value="Hadir" {{ $sekumAbsenEdit->keterangan == 'Hadir' ? "selected" :"Hadir"}}>Hadir</option>
                                                <option value="Izin" {{ $sekumAbsenEdit->keterangan == 'Izin' ? "selected" :"Izin"}}>Izin</option>
                                                <option value="Alpa" {{ $sekumAbsenEdit->keterangan == 'Alpa' ? "selected" :"Alpa"}}>Alpa</option>
                                            </select>
                                            @error('jenis')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </td>
                                    </tr>
                                </table>

                                <input type="text" name="id_absen" value="{{ $sekumAbsenEdit->id_absen }}" readonly hidden>

                                <br> 
                                    <div class="col-12">
                                        <div class="student-submit">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
