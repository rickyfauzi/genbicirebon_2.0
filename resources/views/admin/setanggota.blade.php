@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Pengelolaan Anggota</h3>
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
            {{-- @if (Route::is('admin/user/anggota')) --}}
            <!-- USER ANGGOTA -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-table comman-shadow">
                        <div class="card-body shadow">
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Manajemen Pengaturan Anggota</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('admin/add-anggota') }}" class="btn btn-primary text-white shadow"><i class="fas fa-plus"></i>&nbsp; Tambah</a>
                                          <a href="{{ route('admin/import-anggota') }}" class="btn btn-success text-white shadow"><i class="fas fa-file-excel"></i>&nbsp; Import</a>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="table-responsive">
                                <table class="table border-0 star-student table-hover table-center mb-0 table-striped table-bordered table-sm" id="dtBasicExample">
                                    <thead class="bg-table">   
                                    <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Lengkap</th>
                                            <th>Prodi</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Status</th>
                                            <th>ID Komisariat</th>
                                            <th>ID User</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($adminSetAnggota as $key=>$list)
                                        <tr>
                                            <td class="text-center">{{ ++$key }}</td>                                         
                                            <td>{{ $list->nama }}</td>                                        
                                            <td>{{ $list->prodi }}</td>
                                            <td>{{ $list->jk}}</td>
                                            <td>{{ $list->status}}</td>
                                            <td>
                                                @if($list->id_komisariat == 1)
                                                Koordinator Komisariat Cirebon
                                            @elseif($list->id_komisariat == 2)
                                                Universitas Majalengka
                                            @elseif($list->id_komisariat == 3)
                                                 Universitas Kuningan
                                            @elseif($list->id_komisariat == 4)
                                              Universitas Wiralodra
                                            @elseif($list->id_komisariat == 5)
                                                Universitas Islam Bunga Bangsa Cirebon  
                                            @elseif($list->id_komisariat == 6)
                                                IAIN Syekh Nurjati Cirebon
                                            @elseif($list->id_komisariat == 7)
                                                Universitas Swadaya Gunung Jati Cirebon
                                            @else
                                                No Komisariat
                                            @endif
                                            </td>
                                            <td>{{ $list->id_user}}</td>
                                            <td class="text-center">
                                                    <a href="{{ url('admin/anggota-edit/id_anggota='.$list->id_anggota) }}" class="btn btn-sm bg-warning text-white">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
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
            <!-- END USER ANGGOTA -->
            
        </div>
        
        </div>
  

    {{-- model student delete
    <div class="modal fade contentmodal" id="userdelete" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content doctor-profile">
                <div class="modal-header pb-0 border-bottom-0  justify-content-end">
                    <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close"><i
                        class="feather-x-circle"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin/delete') }}" method="POST">
                        @csrf
                        <div class="delete-wrap text-center">
                            <div class="del-icon">
                                <i class="feather-x-circle"></i>
                            </div>
                            <input type="hidden" name="id_kegiatan" class="e_id" value="">
                            <h2>Yakin ingin menghapus data?</h2>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-success me-2">Yes</button>
                                <a class="btn btn-danger" data-bs-dismiss="modal">No</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
    

    @section('script')
    {{-- delete js --}}
    <script>
        $(document).on('click','.userdelete',function()
        {
            var _this = $(this).parents('tr');
            $('.e_id').val(_this.find('.id').text());
        });   
    </script>



    <script>
        $(document).ready(function () {
            $('#dtBasicExample').DataTable();
            $('.dataTables_length').addClass('bs-select');
        });
    </script>
      
@endsection

@endsection
