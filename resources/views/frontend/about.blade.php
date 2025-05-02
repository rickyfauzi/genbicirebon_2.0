@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Tentang GenBI</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="{{ route('about') }}">Tentang</a>
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

    <!-- ====== About Start ====== -->
    <section id="about" class="bi-tentang">
        <div class="container">
            <div class="bi-tentang-wrapper wow fadeInUp" data-wow-delay=".2s">
                <div class="bi-tentang-content-wrapper">
                    <div class="bi-tentang-content">
                        <span class="tag" data-aos="fade-right" data-aos-duration="1000">About Us</span>
                        <h2 data-aos="fade-right" data-aos-duration="1000">Generasi Baru Indonesia "GenBI"</h2>
                        <p data-aos="fade-right" data-aos-duration="1000">
                            GenBI adalah salah satu bentuk nyata dari Program Sosial Bank Indonesia (PSBI) dalam
                            meningkatkan kualitas mahasiswa sebagai generasi penerus bangsa, yang dibimbing untuk berbagi
                            energi untuk negeri melalui berbagai kegiatan yang dilibatkan masyarakat umum secara langsung.
                        </p>
                        <p data-aos="fade-right" data-aos-duration="1000">
                            Generasi Baru Indonesia, atau yang sering dikenal dengan nama GenBI ialah sebuah komunitas yang
                            terdiri dari mahasiswa-mahasiswa terpilih yang berasal dari beragam latar disiplin ilmu dan
                            keahlian, yang diyakini akan menjadi energi baru yang mampu memberikan kontribusi bagi negara
                            dari berbagai universitas pada sebuah wilayah terpilih sebagai penerima beasiswa. GenBI ini
                            berada langsung dibawah pimpinan oleh pihak Bank Indonesia sendiri.
                        </p>
                    </div>
                </div>
                <div class="bi-tentang-image" data-aos="fade-up" data-aos-duration="1000">
                    <img src="{{ asset('assets2/images/logo.png') }}" alt="about-image" />
                </div>
            </div>
        </div>
    </section>

    <section class="visi-misi">
        <div class="container">
            <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                <span class="tag">Visi & Misi</span>
            </div>
            <div class="vimi-container">
                <div class="visi-section">
                    <h2 data-aos="fade-up-right" data-aos-duration="1000">Visi</h2>
                    <p data-aos="fade-up-right" data-aos-duration="1000">
                        "Menjadi agen perubahan dan penggerak utama dalam pembentukan generasi baru Indonesia yang
                        berkualitas,
                        berintegritas, dan bertanggung jawab, mampu memberikan kontribusi positif bagi kemajuan bangsa dan
                        negara."
                    </p>
                </div>
                <div class="misi-section">
                    <h2 data-aos="fade-up" data-aos-duration="1000">Misi</h2>
                    <ol type="1" data-aos="fade-up" data-aos-duration="1000">
                        <li>Mengagas berbagai kegiatan pemberdayaan masyarakat untuk Indonesia yang lebih baik (Ini)</li>
                        <li>Menjadi garda terdepan dalam melakukan aksi nyata untuk pembangunan bangsa (ACT)</li>
                        <li>Peduli dan berkontribusi untuk pemberdayaan masyarakat (SHARE)</li>
                        <li>Berbagi inspirasi dan motivasi untuk menjadi energi bagi negeri (INSPIRE)</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section id="tujuan" class="ud-tujuan">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                        <span class="tag">Tujuan</span>
                        <h2>Tujuan GenBI</h2>
                    </div>
                </div>
            </div>
            <div class="row g-0 align-items-center justify-content-center">
                <div class="col-lg-4 col-md-6 col-sm-10">
                    <div class="ud-single-tujuan first-item wow fadeInUp" data-wow-delay=".15s" data-aos="fade-up"
                        data-aos-duration="1000">
                        <span class="ud-popular-tag"><i class="lni lni-users icon-tujuan"></i></span>
                        <div class="ud-tujuan-header">
                            <h3>Front Liner</h3>
                        </div>
                        <div class="ud-tujuan-body">
                            <p class="desc-tujuan">
                                Menciptakan generasi muda yang tangguh serta menjadi ujung tombak atau garda terdepan.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-10">
                    <div class="ud-single-tujuan first-item wow fadeInUp" data-wow-delay=".1s" data-aos="fade-up"
                        data-aos-duration="1000">
                        <span class="ud-popular-tag"><i class="lni lni-consulting"></i></span>
                        <div class="ud-tujuan-header">
                            <h3>Agent Of Change</h3>
                        </div>
                        <div class="ud-tujuan-body">
                            <p>Membentuk individu dalam melakukan perubahan positif di masyarakat dan lingkungannya.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-10">
                    <div class="ud-single-tujuan first-item wow fadeInUp" data-wow-delay=".15s" data-aos="fade-up"
                        data-aos-duration="1000">
                        <span class="ud-popular-tag"><i class="lni lni-network"></i></span>
                        <div class="ud-tujuan-header">
                            <h3>Future Leaders</h3>
                        </div>
                        <div class="ud-tujuan-body">
                            <p>Mengembangkan generasi muda yang memiliki kepemimpinan yang kuat dan etika yang baik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ====== About End ====== -->

    <section id="features" class="ud-features">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-section-title" data-aos="fade-up" data-aos-duration="1000">
                        <span class="tag">Divisi</span>
                        <h2>Divisi GenBI Cirebon</h2>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-3 col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="1000">
                    <div class="ud-single-feature wow fadeInUp" data-wow-delay=".1s">
                        <div class="feature-img">
                            <i class="lni lni-graduation"></i>
                        </div>
                        <div class="ud-feature-content">
                            <h3 class="ud-feature-title">Pendidikan</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="1000">
                    <div class="ud-single-feature wow fadeInUp" data-wow-delay=".15s">
                        <div class="feature-img">
                            <i class="lni lni-first-aid"></i>
                        </div>
                        <div class="ud-feature-content">
                            <h3 class="ud-feature-title">Kesehatan</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="1000">
                    <div class="ud-single-feature wow fadeInUp" data-wow-delay=".2s">
                        <div class="feature-img">
                            <i class="lni lni-coin"></i>
                        </div>
                        <div class="ud-feature-content">
                            <h3 class="ud-feature-title">Kewirausahaan</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6" data-aos="fade-up" data-aos-duration="1000">
                    <div class="ud-single-feature wow fadeInUp" data-wow-delay=".25s">
                        <div class="feature-img">
                            <i class="lni lni-grow"></i>
                        </div>
                        <div class="ud-feature-content">
                            <h3 class="ud-feature-title">Lingkungan Hidup</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
