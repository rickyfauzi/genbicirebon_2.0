@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Import Data User</h3>
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
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table shadow">
                        <div class="card-body">
                            <strong>Fitur Ini Dimaksudkan untuk Memasukkan Data Users Awal dan Data Susulan Serta Mengubah Data Users yang Sudah Ada Secara Massal</strong>
                            <p>Mempersiapkan Data dengan Bentuk Excel untuk Diimpor ke dalam Database:</p>
                            <p>
                                <ol>
                                    <li>1. Pastikan Format Data yang Akan Diimpor Sudah Sesuai dengan Aturan Impor Data:</li>
                                    <li>2. Simpan (Save) Berkas Excel sebagai .csv</li>
                                    <li>3. Pastikan Format Excel Ber-Ekstensi .csv (Format Comma delimited)</li>
                                </ol>
                            </p>
                           <!-- resources/views/users/upload-form.blade.php -->
                          
                            <form action="{{ route('admin/user-import')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <label class="form-label" for="file">Pilih File Excel: </label>
                                <input type="file" class="form-control" id="file" name="file" />
                                <div class="col-12 pr-2 pt-3">
                                    <div class="student-submit">
                                        <button class="btn btn-outline-success" data-mdb-ripple-init data-mdb-ripple-color="dark">SIMPAN</button>
                                        <a href="{{ route('admin/user/anggota') }}" class="btn btn-danger"><i class="fas fa-backward"></i> Kembali</a>
                                    </div>
                                </div>
                            </form>
                            
                            </div>
                       
                        </div>
                    </div>
                </div>
            </div>
   



        {{-- footer --}}
        </div>
        </div>


    
@endsection
