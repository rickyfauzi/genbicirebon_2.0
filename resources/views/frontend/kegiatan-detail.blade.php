@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Kegiatan Details</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="#">Kegiatan Details</a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="svg-shape svg-shape-left">
                <img src="{{ asset('assets2/images/line 2.svg') }}" alt="left corner shape">
            </div>
        </div>
    </section>
    <!-- ====== Banner End ====== -->

    <!-- ====== Kegiatan Details Start ====== -->
    <section class="ud-program-details">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-program-details-image">
                        <img src="{{ asset('storage/' . $kegiatan->gambar_kegiatan) }}" alt="program-details" />
                        <div class="ud-program-overlay">
                            <div class="ud-program-overlay-content">
                                <div class="ud-program-meta">
                                    <p class="date">
                                        <i><img src="{{ asset('assets2/images/logo.png') }}" alt=""></i>
                                        <span>{{ $kegiatan->author }}</span>
                                    </p>
                                    <p class="date">
                                        <i class="lni lni-calendar"></i>
                                        <span>{{ \Carbon\Carbon::parse($kegiatan->tanggal_kegiatan)->format('d F Y') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="ud-program-details-content">
                        <h2 class="ud-program-details-title">
                            {{ $kegiatan->nama_kegiatan }}
                        </h2>
                        <p class="ud-program-details-para">
                            {{ $kegiatan->deskripsi }}
                        </p>
                        <h3 class="ud-program-details-subtitle">Tempat Pelaksanaan</h3>
                        <p class="ud-program-details-para">
                            {{ $kegiatan->lokasi }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
