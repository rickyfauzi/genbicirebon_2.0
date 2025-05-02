@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Pengurus GenBI Cirebon</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="#">Pengurus</a>
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

    <!-- ====== Struktur Kepengurusan Start ====== -->
    <section class="ketum mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                        <span class="tag">Organization</span>
                        <h2>Struktur Kepengurusan</h2>
                    </div>
                </div>
            </div>
            <div class="profile-container">
                <!-- Tampilkan 2 kartu pertama -->
                <div class="row">
                    @foreach ($topMembers as $member)
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="profile-card" data-aos="fade-up" data-aos-duration="1000">
                                <img src="{{ asset('assets2/images/dotted-shape.svg') }}" alt="shape"
                                    class="shape shape-1" />
                                <img class="profile-image" src="{{ asset('storage/' . $member->gambar) }}"
                                    alt="Profil Gambar">
                                <div class="profile-name">{{ $member->nama }}</div>
                                <div class="profile-position">{{ $member->jabatan }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Tampilkan 4 kartu sisanya -->
                <div class="row">
                    @foreach ($bottomMembers as $member)
                        <div class="col-lg-3 col-md-6 col-sm-12">
                            <div class="profile-card" data-aos="fade-up" data-aos-duration="1000">
                                <img src="{{ asset('assets2/images/dotted-shape.svg') }}" alt="shape"
                                    class="shape shape-1" />
                                <img class="profile-image" src="{{ asset('storage/' . $member->gambar) }}"
                                    alt="Profil Gambar">
                                <div class="profile-name">{{ $member->nama }}</div>
                                <div class="profile-position">{{ $member->jabatan }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ====== Komisariat GenBI Start ====== -->
    <section class="komisariat-genbi">
        <div class="container">
            <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                <span class="tag">Komisariat</span>
                <h2>Komisariat GenBI Cirebon</h2>
                <p>Saat ini GenBI di wilayah kerja KPwBI Cirebon terdiri dari {{ count($komisariats) - 1 }} komisariat</p>
            </div>
            <div class="logo-univ">
                @foreach ($komisariats as $komisariat)
                    @if ($komisariat->id_komisariat != 1)
                        {{-- Ganti 'id_komisariat' dengan nama kolom ID yang sesuai --}}
                        <div class="komis" data-aos="fade-up" data-aos-duration="1000">
                            <img src="{{ asset('storage/' . $komisariat->image) }}" alt="{{ $komisariat->komisariat }}">
                            <h3>{{ $komisariat->komisariat }}</h3>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
@endsection
