@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Daftar Kegiatan</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="{{ route('kegiatan') }}">Kegiatan</a>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="svg-shape">
                <img src="{{ asset('assets2/images/line 2.svg') }}" alt="left corner shape">
            </div>
        </div>
    </section>
    <!-- ====== Banner End ====== -->

    <!-- ====== Kegiatan Start ====== -->
    <section class="program-kegiatan" id="program-kegiatan">
        <div class="container">
            <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                <span class="tag">Kegiatan</span>
                <h2>Kegiatan GenBI Cirebon</h2>
            </div>
            <div class="row">
                @foreach ($kegiatans as $kegiatan)
                    <div class="col-lg-4 card-kegiatan" data-aos="fade-up" data-aos-duration="1000">
                        <div class="kegiatan post-entry">
                            <div class="genbi-logo">
                                <img src="{{ asset('assets2/images/GenBI original.png') }}" alt="GenBI Logo">
                            </div>
                            <img src="{{ asset('storage/' . $kegiatan->gambar_kegiatan) }}" alt="Gambar">
                            <div class="kegiatan-body">
                                <div>
                                    <span class="text-uppercase font-weight-bold date">
                                        {{ \Carbon\Carbon::parse($kegiatan->tanggal_kegiatan)->format('M d, Y') }}
                                    </span>
                                </div>
                                <h5 class="kegiatan-title">
                                    <a href="{{ route('kegiatan.detail', $kegiatan->id) }}">
                                        {{ $kegiatan->nama_kegiatan }}
                                    </a>
                                </h5>
                                <p>{{ Str::limit($kegiatan->deskripsi, 100) }}</p>
                                <p class="mt-5 mb-0">
                                    <a href="{{ route('kegiatan.detail', $kegiatan->id) }}">
                                        Selengkapnya <i class="lni lni-arrow-right"></i>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="button-center">
                <a class="showLess ud-main-btn ud-link-btn" id="showLess" style="display: none;">Lihat lebih sedikit</a>
                <a class="loadMore ud-main-btn ud-link-btn" id="loadMore">Lihat Lebih Banyak</a>
            </div>
        </div>
    </section>
@endsection
<script>
    $(document).ready(function() {
        var list = $(".card-kegiatan"); // Ambil semua kartu kegiatan
        var numToShowInitial = 6; // Jumlah kartu yang ditampilkan pertama kali
        var numToShowIncrement = 3; // Jumlah kartu yang ditambahkan setiap kali tombol diklik
        var buttonLoadMore = $("#loadMore"); // Tombol "Lihat Lebih Banyak"
        var buttonShowLess = $("#showLess"); // Tombol "Lihat Lebih Sedikit"
        var numInList = list.length; // Total jumlah kartu

        // Sembunyikan semua kartu terlebih dahulu
        list.hide();

        // Tampilkan 6 kartu pertama
        list.slice(0, numToShowInitial).show();

        // Jika total kartu lebih dari 6, tampilkan tombol "Lihat Lebih Banyak"
        if (numInList > numToShowInitial) {
            buttonLoadMore.show();
        }

        // Fungsi untuk menampilkan kartu tambahan
        buttonLoadMore.click(function() {
            var showing = list.filter(":visible").length; // Jumlah kartu yang sedang ditampilkan
            list.slice(showing, showing + numToShowIncrement).fadeIn(); // Tampilkan 3 kartu baru
            var nowShowing = list.filter(":visible").length; // Jumlah kartu yang sekarang ditampilkan

            // Jika semua kartu sudah ditampilkan, sembunyikan tombol "Lihat Lebih Banyak" dan tampilkan tombol "Lihat Lebih Sedikit"
            if (nowShowing >= numInList) {
                buttonLoadMore.hide();
                buttonShowLess.show();
            }
        });

        // Fungsi untuk menyembunyikan kartu tambahan dan kembali ke 6 kartu pertama
        buttonShowLess.click(function() {
            list.hide(); // Sembunyikan semua kartu
            list.slice(0, numToShowInitial).show(); // Tampilkan 6 kartu pertama
            buttonLoadMore.show(); // Tampilkan tombol "Lihat Lebih Banyak"
            buttonShowLess.hide(); // Sembunyikan tombol "Lihat Lebih Sedikit"
        });
    });
</script>
