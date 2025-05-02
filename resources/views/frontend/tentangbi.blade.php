@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Bank Indonesia</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="">Tentang BI</a>
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

    <!-- ====== Bank Indonesia Section Start ====== -->
    <section class="bankindo">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                        <span class="tag">Bank Central Republik Indonesia</span>
                        <h2 class="title-bi">Bank Indonesia</h2>
                    </div>
                </div>
            </div>
            <img src="{{ asset('assets2/images/bigedung.jpg') }}" alt="Bank Indonesia" data-aos="fade-up"
                data-aos-duration="1000">
            <div class="bi-content">
                <div class="desc-bi">
                    <p data-aos="fade-up" data-aos-duration="1000">
                        Bank Indonesia adalah lembaga keuangan yang memiliki peran sentral dalam perekonomian Indonesia.
                        Berikut adalah definisi singkat tentang Bank Indonesia:
                        Bank Indonesia adalah bank sentral Republik Indonesia. Ini adalah institusi keuangan yang
                        bertanggung jawab atas sejumlah tugas penting dalam perekonomian negara, termasuk mengeluarkan dan
                        mengatur mata uang Indonesia (Rupiah), merumuskan dan menjalankan kebijakan moneter, mengawasi
                        sektor perbankan dan sistem pembayaran, serta memelihara stabilitas harga dan stabilitas sistem
                        keuangan.
                        Sebagai bank sentral, Bank Indonesia memiliki peran penting dalam menjaga stabilitas ekonomi negara,
                        melindungi nilai mata uang Rupiah, dan memastikan bahwa sistem keuangan berfungsi dengan baik. Bank
                        Indonesia juga berperan dalam mendorong pertumbuhan ekonomi yang berkelanjutan dan aman bagi negara
                        dan warganya.
                    </p>
                    <h4 class="title-bi" data-aos="fade-right" data-aos-duration="1000">Sebagai Badan Hukum</h4>
                    <p data-aos="fade-right" data-aos-duration="1000">
                        Status Bank Indonesia baik sebagai badan hukum publik maupun badan hukum perdata ditetapkan dengan
                        undang-undang. Sebagai badan hukum publik Bank Indonesia berwenang menetapkan peraturan-peraturan
                        hukum yang merupakan pelaksanaan dari undang-undang yang mengikat seluruh masyarakat luas sesuai
                        dengan tugas dan wewenangnya. Sebagai badan hukum perdata, Bank Indonesia dapat bertindak untuk dan
                        atas nama sendiri di dalam maupun di luar pengadilan.
                    </p>
                    <h4 class="title-bi" data-aos="fade-right" data-aos-duration="1000">Visi Dan Misi</h4>
                    <h3 data-aos="fade-up" data-aos-duration="1000">Visi</h3>
                    <p data-aos="fade-up" data-aos-duration="1000">
                        Menjadi bank sentral digital terdepan yang berkontribusi nyata terhadap perekonomian nasional dan
                        terbaik di antara negara emerging markets untuk Indonesia maju.
                    </p>
                    <h3 data-aos="fade-up" data-aos-duration="1000">Misi</h3>
                    <ol class="misi-bi" data-aos="fade-right" data-aos-duration="1000">
                        <li>Mencapai dan memelihara stabilitas nilai Rupiah melalui efektivitas kebijakan moneter dan bauran
                            Kebijakan Bank Indonesia.</li>
                        <li>Turut menjaga stabilitas sistem keuangan melalui efektivitas kebijakan makroprudensial Bank
                            Indonesia dan sinergi dengan kebijakan mikroprudensial Otoritas Jasa Keuangan.</li>
                        <li>Turut mengembangkan ekonomi dan keuangan digital melalui penguatan kebijakan sistem pembayaran
                            Bank Indonesia dan sinergi dengan kebijakan Pemerintah serta mitra strategis lain.</li>
                        <li>Turut meningkatkan pendalaman pasar keuangan untuk memperkuat efektivitas kebijakan Bank
                            Indonesia dan mendukung pembiayaan ekonomi nasional.</li>
                        <li>Turut mengembangkan ekonomi dan keuangan syariah di tingkat nasional hingga di tingkat daerah.
                        </li>
                        <li>Mewujudkan bank sentral berbasis digital dalam kebijakan dan kelembagaan melalui penguatan
                            organisasi, sumber daya manusia, tata kelola dan sistem informasi yang handal, serta peran
                            internasional yang proaktif.</li>
                    </ol>
                    <h4 class="title-bi" data-aos="fade-up" data-aos-duration="1000">Tujuan Dan Tugas</h4>
                    <h3 data-aos="fade-up" data-aos-duration="1000">Tujuan Tunggal</h3>
                    <p data-aos="fade-right" data-aos-duration="1000">
                        Dalam kapasitasnya sebagai bank sentral, Bank Indonesia mempunyai satu tujuan tunggal, yaitu
                        mencapai dan memelihara kestabilan nilai rupiah. Kestabilan nilai rupiah ini mengandung dua aspek,
                        yaitu kestabilan nilai mata uang terhadap barang dan jasa, serta kestabilan terhadap mata uang
                        negara lain.
                    </p>
                    <h3 data-aos="fade-up" data-aos-duration="1000">Tiga Pilar Utama</h3>
                    <p data-aos="fade-up" data-aos-duration="1000">
                        Untuk mencapai tujuan tersebut Bank Indonesia didukung oleh tiga pilar yang merupakan tiga bidang
                        tugasnya. Adapun tiga pilar tersebut sebagai berikut:
                    </p>
                    <ol data-aos="fade-up" data-aos-duration="1000">
                        <li>Menetapkan dan melaksanakan kebijakan moneter.</li>
                        <li>Mengatur dan menjaga kelancaran sistem pembayaran, serta.</li>
                        <li>Menjaga stabilitas sistem keuangan.</li>
                    </ol>
                    <h4 class="title-bi" data-aos="fade-right" data-aos-duration="1000">Fungsi Utama</h4>
                    <h3 data-aos="fade-up" data-aos-duration="1000">Moneter</h3>
                    <p data-aos="fade-right" data-aos-duration="1000">
                        Bank Indonesia memiliki tujuan untuk mencapai dan memelihara kestabilan nilai Rupiah. Tujuan ini
                        sebagaimana tercantum dalam UU No. 23 Tahun 1999 tentang Bank Indonesia, yang sebagaimana diubah
                        melalui UU No. 3 Tahun 2004 dan UU No. 6 Tahun 2009 pada pasal 7. Kestabilan Rupiah yang dimaksud
                        mempunyai dua dimensi. Dimensi pertama kestabilan nilai Rupiah adalah kestabilan terhadap
                        harga-harga barang dan jasa yang tercermin dari perkembangan laju inflasi. Sementara itu, dimensi
                        kedua terkait dengan kestabilan nilai tukar Rupiah terhadap mata uang negara lain. Indonesia
                        menganut sistem nilai tukar mengambang (free floating). Namun, peran kestabilan nilai tukar sangat
                        penting dalam mencapai stabilitas harga dan sistem keuangan.
                    </p>
                    <h3 data-aos="fade-up" data-aos-duration="1000">Stabilitas Sistem Keuangan</h3>
                    <p data-aos="fade-right" data-aos-duration="1000">
                        Stabilitas sistem keuangan adalah suatu kondisi yang memungkinkan sistem keuangan nasional berfungsi
                        efektif dan efisien serta mampu bertahan terhadap kerentanan internal dan eksternal, sehingga
                        alokasi sumber pendanaan atau pembiayaan dapat berkontribusi pada pertumbuhan dan stabilitas
                        perekonomian nasional.
                    </p>
                    <h3 data-aos="fade-up" data-aos-duration="1000">Sistem Pembayaran & Pengelolaan Uang Rupiah</h3>
                    <p data-aos="fade-right" data-aos-duration="1000">
                        Pembayaran adalah sistem yang mencakup seperangkat aturan, lembaga, dan mekanisme yang dipakai untuk
                        melaksanakan pemindahan dana, guna memenuhi suatu kewajiban yang timbul dari suatu kegiatan ekonomi.
                        Sistem Pembayaran lahir bersamaan dengan lahirnya konsep 'uang' sebagai media pertukaran (medium of
                        change) atau intermediary dalam transaksi barang, jasa dan keuangan. Pada prinsipnya, sistem
                        pembayaran memiliki 3 tahap pemrosesan yaitu otorisasi, kliring, dan penyelesaian akhir
                        (settlement).
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ====== KPWBI Section Start ====== -->
    <section class="kpwbi" id="kpwbi">
        <div class="container">
            <div class="title-kpwbi" data-aos="fade-up" data-aos-duration="1000">
                <span class="tag">KPWBI Cirebon</span>
                <h3>Cirebon Province Bank Indonesia Representative Office</h3>
            </div>
            <img src="{{ asset('assets2/images/pa hestu.png') }}" alt="Gambar KPWBI" class="section-image" data-aos="fade-up"
                data-aos-duration="1000">
            <h2 class="title-kpwbi" data-aos="fade-up" data-aos-duration="1000">HESTU WIBOWO</h2>
            <p class="desc-kpwbi" data-aos="fade-up" data-aos-duration="1000">Deputy Director – Head of Province Bank
                Indonesia Representative Office</p>
        </div>
    </section>
@endsection
