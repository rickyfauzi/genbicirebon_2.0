
@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-sub-header">
                            <h3 class="page-title">Galeri Manajemen Kegiatan GenBI Cirebon</h3>
                            <ul class="breadcrumb">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <span> <i class="fas fa-image"></i> Galeri</span>
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
                            <div class="page-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h3 class="page-title">Galeri GenBi Cirebon</h3>
                                    </div>
                                    <div class="col-auto text-end float-end ms-auto download-grp">
                                        <a href="{{ route('admin/galery/create')}}" class="btn btn-outline-primary me-2"><i class="fas fa-plus"></i>
                                            Tambah Galeri</a>
                                    </div>
                                </div>
                            </div>
                            <nav aria-label="Page navigation example">
                              <ul class="pagination justify-content-center">
                                  {{-- Previous Page Link --}}
                                  @if ($galeries->onFirstPage())
                                      <li class="page-item disabled">
                                          <span class="page-link">Sebelumnya</span>
                                      </li>
                                  @else
                                      <li class="page-item">
                                          <a class="page-link" href="{{ $galeries->previousPageUrl() }}" tabindex="-1">Sebelumnya</a>
                                      </li>
                                  @endif
                          
                                  {{-- Pagination Elements --}}
                                  @for ($i = 1; $i <= $galeries->lastPage(); $i++)
                                      <li class="page-item {{ ($galeries->currentPage() == $i) ? 'active' : '' }}">
                                          <a class="page-link" href="{{ $galeries->url($i) }}">{{ $i }}</a>
                                      </li>
                                  @endfor
                          
                                  {{-- Next Page Link --}}
                                  @if ($galeries->hasMorePages())
                                      <li class="page-item">
                                          <a class="page-link" href="{{ $galeries->nextPageUrl() }}">Berikutnya</a>
                                      </li>
                                  @else
                                      <li class="page-item disabled">
                                          <span class="page-link">Berikutnya</span>
                                      </li>
                                  @endif
                              </ul>
                          </nav>
                            <div class="row">
                                @foreach ($galeries as $gallery)
                                    <div class="col-md-4 mb-2 mt-2 pb-2">
                                        <div class="card mb-6 pb-5">
                                          <img src="{{ asset('storage/' . $gallery->image_path) }}" alt="{{ $gallery->title }}">
                                            <div class="card-body shadow border-radius: 20px; ">
                                                <h5 class="card-title">{{ $gallery->title }}</h5>
                                                <p style="text-align: justify;" class="card-text">{{strip_tags($gallery->description)}}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                               
                            </div>

                           
                          
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- END ABSEN KEGIATAN UTAMA -->
@endsection
