@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Halaman Blog</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="{{ route('blog') }}">Blog</a>
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

    <!-- ====== Blog Start ====== -->
    <section class="ud-blog-grids">
        <div class="container">
            <div class="ud-section-title mx-auto text-center">
                <span class="tag" data-aos="fade-up" data-aos-duration="1000">Event</span>
                <h2 data-aos="fade-up" data-aos-duration="1000">Berita</h2>
            </div>
            <div class="row">
                @foreach ($blogs as $blog)
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-duration="1000">
                        <div class="ud-single-blog">
                            <div class="ud-blog-image">
                                <a href="{{ route('blog.detail', $blog->id) }}">
                                    <img src="{{ asset('storage/' . $blog->gambar) }}" alt="blog" />
                                </a>
                            </div>
                            <div class="ud-blog-content">
                                <span
                                    class="ud-blog-date">{{ \Carbon\Carbon::parse($blog->tanggal_blog)->format('d F Y') }}</span>
                                <h3 class="ud-blog-title">
                                    <a href="{{ route('blog.detail', $blog->id) }}">
                                        {{ $blog->nama_blog }}
                                    </a>
                                </h3>
                                <p class="ud-blog-desc">
                                    {{ $blog->deskripsi1 }}
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
