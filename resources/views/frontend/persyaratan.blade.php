@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Persyaratan Beasiswa BI</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="">Persyaratan Beasiswa</a>
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

    <!-- ====== Syarat Beasiswa Start ====== -->
    <section class="syarat">
        <div class="container">
            <div class="row">
                <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                    <span class="tag">Beasiswa</span>
                    <h2>Persyaratan</h2>
                </div>
                <div class="syarat-desc">
                    <div class="deskripsi-content">
                        <p data-aos="zoom-in" data-aos-duration="1000">
                            Beasiswa Bank Indonesia adalah program beasiswa yang diselenggarakan oleh Bank Indonesia, bank
                            sentral Republik Indonesia. Program ini ditujukan bagi para mahasiswa yang berprestasi untuk
                            mendukung pendidikan dan pengembangan sumber daya manusia yang unggul di Indonesia.

                            Tujuan dari Beasiswa Bank Indonesia adalah memberikan kesempatan kepada mahasiswa berprestasi
                            untuk melanjutkan pendidikan ke jenjang yang lebih tinggi, baik dalam negeri maupun di luar
                            negeri. Selain itu, beasiswa ini juga bertujuan untuk mendukung pertumbuhan dan pengembangan
                            sumber daya manusia yang berkualitas di bidang ekonomi, keuangan, dan perbankan.

                            Detail mengenai kriteria, proses pendaftaran, manfaat, dan persyaratan untuk mendapatkan
                            beasiswa Bank Indonesia dapat bervariasi dari satu program ke program lainnya. Biasanya,
                            beasiswa ini mencakup biaya pendidikan, biaya hidup, buku, dan fasilitas lain yang diperlukan
                            selama masa studi.

                            Beasiswa Bank Indonesia merupakan kesempatan yang sangat berharga bagi mahasiswa yang memiliki
                            kemampuan akademik yang tinggi dan memiliki komitmen untuk berkontribusi pada pengembangan
                            ekonomi dan keuangan di Indonesia.
                        </p>
                    </div>
                    <div class="content-container">
                        <!-- Konten Kanan -->
                        <div class="content-right">
                            <h3 class="title-syarat" data-aos="fade-up" data-aos-duration="1000">Dokumen Administratif</h3>
                            <ul class="bulat" data-aos="fade-up-right" data-aos-duration="1000">
                                <li>Kartu Tanda Mahasiswa (KTM)</li>
                                <li>Kartu Tanda Penduduk (KTP)</li>
                                <li>Pas Photo ukuran 4x3 berlatar biru atau merah</li>
                                <li>Kartu Keluarga (KK)</li>
                                <li>Profil terdaftar aktif di <a
                                        href="https://pddikti.kemdikbud.go.id">https://pddikti.kemdikbud.go.id</a></li>
                                <li>Transkrip nilai terbaru yang di cap dan ditandatangani pihak fakultas</li>
                                <li>Resume pribadi dalam bahasa Indonesia</li>
                                <li>Motivation letter dalam bahasa Indonesia</li>
                                <li>Form. A.1 BI (terlampir pada link pendaftaran)</li>
                                <li>Surat pernyataan tidak sedang menerima beasiswa dan/atau bantuan jenis lainnya dan siap
                                    menjadi bagian dari GenBI (terlampir pada link pendaftaran)</li>
                                <li>Surat Rekomendasi dari 1 (Satu) tokoh akademik/non-akademik (terlampir pada link
                                    pendaftaran)</li>
                                <li>Surat Keterangan Tidak Mampu (SKTM) dari desa/kelurahan setempat</li>
                            </ul>
                        </div>

                        <!-- Konten Kiri -->
                        <div class="content-left" data-aos="fade-up" data-aos-duration="1000">
                            <h3 class="title-syarat" data-aos="fade-up" data-aos-duration="1000">Kriteria</h3>
                            <ul class="bulat" data-aos="fade-up" data-aos-duration="1000">
                                <li>Minimal Semester 3 (tiga) dan Telah Menyelesaikan Sekurang-kurangnya 40 (empat puluh)
                                    SKS</li>
                                <li>Maksimal Berusia 23 Tahun pada Bulan November 2023</li>
                                <li>Tidak Sedang Menerima Beasiswa atau Bantuan Lainnya dan/atau Berada dalam Status Ikatan
                                    Dinas dari Lembaga/Instansi Lain</li>
                                <li>Memiliki Pengalaman Menjalankan Aktivitas Sosial yang Membawa Dampak Positif bagi
                                    Masyarakat</li>
                                <li>Bersedia untuk Berperan Aktif, Mengelola, dan Mengembangkan Generasi Baru Indonesia
                                    (GenBI) serta Berpartisipasi dalam Kegiatan yang Diselenggarakan oleh Bank Indonesia dan
                                    Universitas Majalengka</li>
                                <li>Memiliki IPK Minimal 3.25 (Skala 4)</li>
                                <li>Prioritas diberikan kepada Mahasiswa dari Latar Belakang Ekonomi Keluarga Kurang Mampu,
                                    Tetapi Memiliki Prestasi Akademik/Non-Akademik yang Baik</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====== Program Studi Prioritas Start ====== -->
    <section class="prodi">
        <div class="container">
            <h2 data-aos="fade-up" data-aos-duration="1000">Program Studi Prioritas</h2>
            <div class="prodi-content">
                <ul data-aos="fade-up-right" data-aos-duration="1000">
                    <li>Manajemen</li>
                    <li>Akuntansi</li>
                    <li>Ekonomi Syari’ah</li>
                    <li>Pendidikan Matematika</li>
                    <li>Agribisnis</li>
                    <li>Agroteknologi</li>
                    <li>Peternakan</li>
                    <li>Ilmu Hukum</li>
                    <li>Administrasi Publik</li>
                    <li>Ilmu Komunikasi</li>
                    <li>Informatika</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ====== Timeline Start ====== -->
    <section class="timeline">
        <div class="container">
            <h2 data-aos="fade-up" data-aos-duration="1000">Timeline</h2>
            <div class="timeline-container">
                <!-- Item 1 -->
                <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000">
                    <div class="timeline-content">
                        <h3>Pendaftaran</h3>
                        <p>Unggah / submit berkas</p>
                    </div>
                </div>
                <!-- Item 2 -->
                <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000">
                    <div class="timeline-content">
                        <h3>Seleksi Tahap I</h3>
                        <p>Berkas Administrasi</p>
                    </div>
                </div>
                <!-- Item 3 -->
                <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000">
                    <div class="timeline-content">
                        <h3>Seleksi Tahap II</h3>
                        <p>Wawancara</p>
                    </div>
                </div>
                <!-- Item 4 -->
                <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000">
                    <div class="timeline-content">
                        <h3>Pengumuman</h3>
                        <p>Hasil Akhir</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
