<!-- ====== Header Start ====== -->
<!-- ====== Header End ====== -->

<!-- ====== Hero Start ====== -->
<!DOCTYPE html>
<html lang="en">


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
{{-- <title>Document</title> --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('assets2/css/animate.css') }}" />
<link rel="stylesheet" href="{{ asset('assets2/css/lineicons.css') }}" />
<link rel="stylesheet" href="{{ asset('assets2/css/style.css') }}" />
<link rel="stylesheet" href="{{ asset('assets2/css/aos.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css" />

</head>

<body>
    @extends('frontend.layouts.app')

    @section('content')
        <section class="ud-hero" id="home">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ud-hero-content wow fadeInUp" data-wow-delay=".2s">
                            <h1 class="ud-hero-title">
                                Selamat Datang di Website GenBI Cirebon, Portal Berita dan Sistem Absensi Terintegrasi
                            </h1>
                            <p class="ud-hero-desc">
                                <span id="typed-text-1">GenBI, Energi untuk Negeri </span><br>
                                <span id="typed-text-2">GenBI Cirebon, Semangat Kita Membangun Bangsa</span>
                            </p>
                            <ul class="ud-hero-buttons">
                                <li>
                                    <a href="{{ url('satugenbi/public') }}" rel="nofollow noopener" target="_blank"
                                        class="ud-main-btn ud-white-btn">
                                        Gabung Sekarang
                                    </a>
                                </li>
                                <li>
                                    <a href="#" rel="nofollow noopener" target="_blank"
                                        class="ud-main-btn ud-link-btn">
                                        Selengkapnya <i class="lni lni-arrow-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Hero Images -->
                        <div class="ud-hero-image wow fadeInUp" data-wow-delay=".25s">
                            <img src="{{ asset('assets2/images/dash.png') }}" alt="hero-image" />
                        </div>

                        <!-- Decorative SVG shapes for the corners -->
                        <div class="svg-shape svg-shape-left">
                            <img src="{{ asset('assets2/images/line 2.svg') }}" alt="left corner shape">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="logos-sec">
            <div class="container">
                <div class="logos">
                    <div class="logos-slide">
                        @php
                            // List of images
                            $images = [
                                'assets2/images/bi-b.png',
                                'assets2/images/logo.png',
                                'assets2/images/bi-b.png',
                                'assets2/images/logo.png',
                                'assets2/images/bi-b.png',
                                'assets2/images/logo.png',
                            ];

                            // Display images 4 times to create a continuous loop
                            for ($i = 0; $i < 4; $i++) {
                                foreach ($images as $image) {
                                    echo '<img src="' . asset($image) . '" alt="Logo">';
                                }
                            }
                        @endphp
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="bi-about">
            <div class="container">
                <div class="bi-about-wrapper">
                    <div class="bi-about-content-wrapper">
                        <div class="bi-about-content" data-aos="fade-right" data-aos-duration="1000">
                            <span class="tag">About Us</span>
                            <h2>Generasi Baru Indonesia "GenBI"</h2>
                            <p>
                                GenBI adalah salah satu bentuk nyata dari Program Sosial Bank Indonesia (PSBI) dalam
                                meningkatkan kualitas mahasiswa sebagai generasi penerus bangsa, yang dibimbing untuk
                                berbagi energi untuk negeri melalui berbagai kegiatan yang dilibatkan masyarakat umum
                                secara
                                langsung.
                            </p>
                            <p class="genbi-2">
                                Generasi Baru Indonesia, atau yang sering dikenal dengan nama GenBI ialah sebuah
                                komunitas
                                yang terdiri dari mahasiswa-mahasiswa terpilih yang berasal dari beragam latar disiplin
                                ilmu
                                dan keahlian, yang diyakini akan menjadi energi baru yang mampu memberikan kontribusi
                                bagi
                                negara dari berbagai universitas pada sebuah wilayah terpilih sebagai penerima beasiswa.
                                GenBI ini berada langsung dibawah pimpinan oleh pihak Bank Indonesia sendiri
                            </p>
                            {{-- <a href="{{ route('about') }}" class="ud-main-btn">Selengkapnya</a> --}}
                        </div>
                    </div>
                    <div class="bi-about-image" data-aos="fade-up" data-aos-duration="1000">
                        <img src="{{ asset('assets2/images/logo.png') }}" alt="about-image" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ====== Hero End ====== -->

        <!-- ====== About Start ====== -->
        <!-- ====== About End ====== -->

        <!-- ====== Blog Start ====== -->
        <section class="ud-blog-grids">
            <div class="container">
                <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                    <span class="tag">Blog</span>
                    <h2>Berita Terbaru</h2>
                </div>
                <div class="row">
                    @foreach ($blogs as $blog)
                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="1000">
                            <div class="ud-single-blog">
                                <div class="ud-blog-image">
                                    <a href="{{ url('blog-details/' . $blog->id) }}">
                                        <img src="{{ asset('storage/' . $blog->gambar) }}" alt="blog" />
                                    </a>
                                </div>
                                <div class="ud-blog-content">
                                    <span class="ud-blog-date">{{ date('d F Y', strtotime($blog->tanggal_blog)) }}</span>
                                    <h3 class="ud-blog-title">
                                        <a href="{{ url('home/blogd/' . $blog->id) }}">
                                            {{ $blog->nama_blog }}
                                        </a>
                                    </h3>
                                    <p class="ud-blog-desc">
                                        {{ substr($blog->deskripsi1, 0, 100) }}...
                                    </p>
                                    <p class="mt-5 mb-0">
                                        <a href="{{ url('home/blogd/' . $blog->id) }}">Selengkapnya <i
                                                class="lni lni-arrow-right"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="button-center" data-aos="zoom-in">
                    <a href="{{ url('home/blog') }}" class="ud-main-btn ud-link-btn">
                        Lihat Lebih Banyak <i class="lni lni-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <section class="program-kegiatan" id="program-kegiatan">
            <div class="container">
                <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                    <span class="tag">Kegiatan</span>
                    <h2>Daftar Kegiatan</h2>
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
                                    <div><span
                                            class="text-uppercase font-weight-bold date">{{ date('M d, Y', strtotime($kegiatan->tanggal_kegiatan)) }}</span>
                                    </div>
                                    <h5 class="kegiatan-title">
                                        <a
                                            href="{{ url('home/programd/' . $kegiatan->id) }}">{{ $kegiatan->nama_kegiatan }}</a>
                                    </h5>
                                    <p>{{ substr($kegiatan->deskripsi, 0, 100) }}...</p>
                                    <p class="mt-5 mb-0">
                                        <a href="{{ url('home/programd/' . $kegiatan->id) }}">Selengkapnya <i
                                                class="lni lni-arrow-right"></i></a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="button-center">
                    <a href="{{ url('home/program') }}" class="ud-main-btn ud-link-btn" data-aos="zoom-in">
                        Lihat Lebih Banyak <i class="lni lni-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <section class="testimonial-section">
            <div class="container">
                <div class="testimonial-container">
                    <div class="testimonial-image" data-aos="fade-right" data-aos-duration="1000">
                        <img src="{{ asset('assets2/images/file (2).png') }}" alt="Kesan Pengguna">
                    </div>
                    <div class="testimonial-content" data-aos="fade-right" data-aos-duration="1000">
                        <h2>M Taufan Gemilang</h2>
                        <span class="span-testi">Ketua Umum GenBI Cirebon</span>
                        <p class="" data-aos="fade-left" data-aos-duration="1000"> "
                            saya selama menjadi anggota GenBI, banyak ilmu baru yang saya dapat, banyak teman baru yang
                            saya
                            kenal, dan bisa bertemu orang hebat. namun GenBI juga banyak mengajarkan tentang
                            mempersiapkan
                            kita untuk menghadapi masa depan yang lebih baik.
                            "</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- faq-->
        <section class="faq-section">
            <div class="container">
                <div class="row">
                    <!-- ***** FAQ Start ***** -->
                    <div class="col-md-6 offset-md-3">
                        <div class="ud-section-title mx-auto text-center" data-aos="fade-up" data-aos-duration="1000">
                            <span class="tag">FAQ</span>
                            <h2>Any Questions? Answered</h2>
                        </div>
                    </div>
                    <div class="col-md-6 offset-md-3">
                        <div class="faq" id="accordion">
                            <div class="card">
                                <div class="card-header" id="faqHeading-1">
                                    <div class="mb-0">
                                        <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-1"
                                            data-aria-expanded="true" data-aria-controls="faqCollapse-1">
                                            <span class="badge">1</span>Apa itu GenBI?
                                        </h5>
                                    </div>
                                </div>
                                <div id="faqCollapse-1" class="collapse" aria-labelledby="faqHeading-1"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <p>Generasi Baru Indonesia (GenBI) adalah komunitas mahasiswa penerima beasiswa
                                            dari
                                            Bank Indonesia yang dibentuk di berbagai perguruan tinggi di Indonesia.
                                            Komunitas ini didirikan pada tanggal 11 November 2011 dan dikelola oleh para
                                            mahasiswa penerima beasiswa Bank Indonesia. </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="faqHeading-2">
                                    <div class="mb-0">
                                        <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-2"
                                            data-aria-expanded="false" data-aria-controls="faqCollapse-2">
                                            <span class="badge">2</span> Apa saja tujuan GenBI ?
                                        </h5>
                                    </div>
                                </div>
                                <div id="faqCollapse-2" class="collapse" aria-labelledby="faqHeading-2"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <p>GenBI bertujuan untuk meningkatkan kepekaan sosial masyarakat, menumbuhkan
                                            semangat kepemimpinan, serta membimbing generasi muda agar dapat
                                            berkontribusi
                                            dalam pembangunan sosial, ekonomi, budaya, dan lingkungan di Indonesia.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="faqHeading-3">
                                    <div class="mb-0">
                                        <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-3"
                                            data-aria-expanded="false" data-aria-controls="faqCollapse-3">
                                            <span class="badge">3</span>Bagaimana GenBI berperan dalam masyarakat?
                                        </h5>
                                    </div>
                                </div>
                                <div id="faqCollapse-3" class="collapse" aria-labelledby="faqHeading-3"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <p>Peran GenBI adalah menginspirasi, membimbing, dan memberdayakan generasi muda
                                            agar dapat menjadi agen perubahan positif dalam pembangunan Indonesia, baik
                                            dalam skala lokal maupun nasional.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="faqHeading-4">
                                    <div class="mb-0">
                                        <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-4"
                                            data-aria-expanded="false" data-aria-controls="faqCollapse-4">
                                            <span class="badge">4</span>komisariat yang tergabung dengan GenBI
                                            Cirebon?
                                        </h5>
                                    </div>
                                </div>
                                <div id="faqCollapse-4" class="collapse" aria-labelledby="faqHeading-4"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <p>GenBI Cirebon memiliki enam komisariat yang aktif, yaitu:
                                            Komisariat IAIN Syekh Nurjati Cirebon,
                                            Komisariat UI Bunga Bangsa Cirebon,
                                            Komisariat Universitas Majalengka,
                                            Komisariat Universitas Kuningan,
                                            Komisariat Universitas Wiralodra, dan
                                            komisariat universitas Swadaya Gunung Jati</p>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="faqHeading-5">
                                    <div class="mb-0">
                                        <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-5"
                                            data-aria-expanded="false" data-aria-controls="faqCollapse-5">
                                            <span class="badge">5</span> Berapa jumlah anggota GenBI Cirebon saat ini?
                                        </h5>
                                    </div>
                                </div>
                                <div id="faqCollapse-5" class="collapse" aria-labelledby="faqHeading-5"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <p>
                                            Saat ini, GenBI Cirebon memiliki sekitar 300 anggota yang terorganisir dalam
                                            6
                                            komisariat atau cabang di beberapa universitas di Cirebon dan sekitarnya.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header" id="faqHeading-6">
                                    <div class="mb-0">
                                        <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-6"
                                            data-aria-expanded="false" data-aria-controls="faqCollapse-6">
                                            <span class="badge">6</span> Bagaimana cara bergabung dengan GenBI
                                            Cirebon?
                                        </h5>
                                    </div>
                                </div>
                                <div id="faqCollapse-6" class="collapse" aria-labelledby="faqHeading-6"
                                    data-parent="#accordion">
                                    <div class="card-body">
                                        <p>
                                            Untuk bergabung dengan GenBI Cirebon, biasanya ada proses penerimaan anggota
                                            yang diumumkan oleh setiap komisariat atau cabang GenBI di perguruan tinggi.
                                            Biasanya, penerimaan anggota melibatkan proses seleksi dan pendaftaran yang
                                            dapat diikuti oleh mahasiswa di perguruan tinggi yang terkait.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ajakan position-relative text-center text-white py-5">
            <div class="overlay"></div>
            <div class="container position-relative">
                <div class="row justify-content-center">
                    <div class="col-lg-8" data-aos="fade-up" data-aos-duration="1000">
                        <h1 class="display-4">Energi<span>Untuk Negri</span></h1>
                        <p class="lead mb-4">Kepemimpinan Berwawasan, Agen Perubahan, dan Garda Terdepan untuk Masa
                            Depan"
                        </p>
                        <a href="#" class="btn btn-main btn-lg btn-primary">Mulai sekarang <i
                                class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <section class="youtube">
            <div class="container">
                <div class="youtube-section">
                    <div class="youtube-content">
                        <div class="youtube-info">
                            <h1 class="youtube-title">SUBSCRIBE YOUTUBE<span> GENBI CIREBON</span></h1>
                            <p class="youtube-description">Dokumentasi kegiatan – kegiatan GenBI Cirebon dalam bentuk
                                video
                                yang diunggah di channel youtube GenBI Cirebon</p>
                        </div>
                        <div class="youtube-thumbnail">
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/2F-L9SHgBqk"
                                frameborder="0" allowfullscreen></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>



        {{-- <a href="javascript:void(0)" class="back-to-top">
            <i class="lni lni-chevron-up"></i> <!-- Ikon back-to-top -->
        </a> --}}

        <!-- ====== Footer Start ====== -->
        <!-- ====== Footer End ====== -->

        <!-- ====== Back To Top Start ====== -->

        <!-- ====== Back To Top End ====== -->

        <!-- ====== All Javascript Files ====== -->
        <script src="{{ asset('assets2/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets2/js/wow.min.js') }}"></script>
        <script src="{{ asset('assets2/js/typed.js') }}"></script>
        <script src="{{ asset('assets2/js/main.js') }}"></script>
        <script src="{{ asset('assets2/js/aos.js') }}"></script>
        <script src="{{ asset('assets2/js/change.js') }}"></script>

        <script>
            const submenuButton = document.querySelectorAll(".nav-item-has-children");
            submenuButton.forEach((elem) => {
                elem.querySelector("a").addEventListener("click", () => {
                    elem.querySelector(".ud-submenu").classList.toggle("show");
                });
            });
        </script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Ambil semua elemen dengan class faq-title
                const faqTitles = document.querySelectorAll('.faq-title');

                // Loop melalui setiap judul FAQ
                faqTitles.forEach(title => {
                    // Tambahkan event listener untuk setiap judul FAQ
                    title.addEventListener('click', () => {
                        const collapseID = title.getAttribute('data-target');

                        // Toggle class 'collapsed' pada judul FAQ
                        title.classList.toggle('collapsed');

                        // Ambil elemen yang di-target
                        const collapseElement = document.querySelector(collapseID);

                        // Toggle class 'show' pada elemen yang di-target
                        collapseElement.classList.toggle('show');
                    });
                });
            });
        </script>

        <script>
            AOS.init();
        </script>

        <script>
            const titleText = "Selamat Datang di Website GenBI Cirebon";
            const descTexts = ["GenBI, Energi untuk Negeri", " GenBI Cirebon, Semangat Kita Membangun Bangsa"];

            let titleIndex = 0;
            let descIndex = 0;
            let charIndexTitle = 0;
            let charIndexDesc = 0;

            function typeWriterTitle() {
                const titleElement = document.querySelector('.ud-hero-title .title-text');
                titleElement.textContent += titleText[charIndexTitle];
                charIndexTitle++;

                if (charIndexTitle < titleText.length) {
                    setTimeout(typeWriterTitle, 100);
                } else {
                    setTimeout(() => {
                        typeWriterDesc();
                    }, 1000); // Tunggu 1 detik setelah selesai mengetik judul
                }
            }

            function typeWriterDesc() {
                const descElement1 = document.getElementById('typed-text-1');
                const descElement2 = document.getElementById('typed-text-2');

                if (descIndex === 0) {
                    descElement1.textContent += descTexts[descIndex][charIndexDesc];
                    charIndexDesc++;

                    if (charIndexDesc < descTexts[descIndex].length) {
                        setTimeout(typeWriterDesc, 100);
                    } else {
                        descIndex++;
                        charIndexDesc = 0;
                        setTimeout(typeWriterDesc, 1000);
                    }
                } else if (descIndex === 1) {
                    descElement2.textContent += descTexts[descIndex][charIndexDesc];
                    charIndexDesc++;

                    if (charIndexDesc < descTexts[descIndex].length) {
                        setTimeout(typeWriterDesc, 100);
                    }
                }
            }

            // Memulai animasi
            document.addEventListener("DOMContentLoaded", function() {
                typeWriterTitle();
            });
        </script>

        <script>
            // ==== for menu scroll
            const pageLink = document.querySelectorAll(".ud-menu-scroll");

            pageLink.forEach((elem) => {
                elem.addEventListener("click", (e) => {
                    e.preventDefault();
                    document.querySelector(elem.getAttribute("href")).scrollIntoView({
                        behavior: "smooth",
                        offsetTop: 1 - 60,
                    });
                });
            });

            // section menu active
            function onScroll(event) {
                const sections = document.querySelectorAll(".ud-menu-scroll");
                const scrollPos =
                    window.pageYOffset ||
                    document.documentElement.scrollTop ||
                    document.body.scrollTop;

                for (let i = 0; i < sections.length; i++) {
                    const currLink = sections[i];
                    const val = currLink.getAttribute("href");
                    const refElement = document.querySelector(val);
                    const scrollTopMinus = scrollPos + 73;
                    if (
                        refElement.offsetTop <= scrollTopMinus &&
                        refElement.offsetTop + refElement.offsetHeight > scrollTopMinus
                    ) {
                        document
                            .querySelector(".ud-menu-scroll")
                            .classList.remove("active");
                        currLink.classList.add("active");
                    } else {
                        currLink.classList.remove("active");
                    }
                }
            }

            window.document.addEventListener("scroll", onScroll);
        </script>

        <script>
            const items = document.querySelectorAll(".accordion button");

            function toggleAccordion() {
                const itemToggle = this.getAttribute('aria-expanded');

                for (i = 0; i < items.length; i++) {
                    items[i].setAttribute('aria-expanded', 'false');
                }

                if (itemToggle == 'false') {
                    this.setAttribute('aria-expanded', 'true');
                }
            }

            items.forEach(item => item.addEventListener('click', toggleAccordion));
        </script>

        <script>
            const items = document.querySelectorAll(".accordion button");

            function toggleAccordion() {
                const itemToggle = this.getAttribute('aria-expanded');

                for (i = 0; i < items.length; i++) {
                    items[i].setAttribute('aria-expanded', 'false');
                }

                if (itemToggle == 'false') {
                    this.setAttribute('aria-expanded', 'true');
                }
            }

            items.forEach(item => item.addEventListener('click', toggleAccordion));
        </script>



    </body>

    </html>
