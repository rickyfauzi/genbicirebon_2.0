@extends('frontend.layouts.app')

@section('content')
    <!-- ====== Banner Start ====== -->
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Blog Details</h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="banner-link-nav">
                                <a class="active" href="#">Blog Details</a>
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

    <!-- ====== Blog Details Start ====== -->
    <section class="ud-blog-details">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-blog-details-image">
                        <img src="{{ asset('storage/' . $blog->gambar) }}" alt="blog details" />
                        <div class="ud-blog-overlay">
                            <div class="ud-blog-overlay-content">
                                <div class="ud-blog-author">
                                    <p class="date">
                                        <i><img src="{{ asset('assets2/images/logo.png') }}" alt="author"></i>
                                        <span>{{ $blog->author }}</span>
                                    </p>
                                    <p class="date">
                                        <i class="lni lni-calendar"></i>
                                        <span>{{ \Carbon\Carbon::parse($blog->tanggal_blog)->format('d F Y') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="ud-blog-details-content">
                        <h2 class="ud-blog-details-title">
                            {{ $blog->nama_blog }}
                        </h2>
                        <p class="ud-blog-details-para">
                            {{ $blog->deskripsi1 }}
                        </p>
                        <p class="ud-blog-details-para">
                            {{ $blog->deskripsi2 }}
                        </p>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="ud-blog-sidebar">
                        <div class="ud-articles-box">
                            <h3 class="ud-articles-box-title">Artikel Populer</h3>
                            <ul class="ud-articles-list">
                                @foreach ($popularArticles as $article)
                                    <li>
                                        <div class="ud-article-image">
                                            <img src="{{ asset('storage/' . $article->gambar) }}" alt="author" />
                                        </div>
                                        <div class="ud-article-content">
                                            <h5 class="ud-article-title">
                                                <a href="{{ route('blog.detail', $article->id) }}">
                                                    {{ $article->nama_blog }}
                                                </a>
                                            </h5>
                                            <p class="ud-article-author">{{ $article->author }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="ud-banner-ad">
                            <a href="javascript:void(0)">
                                <img src="{{ asset('assets2/images/blog/banner-ad.png') }}" alt="ad banner" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ====== Related Articles Start ====== -->
    <section class="ud-blog-grids ud-related-articles">
        <div class="container">
            <div class="row col-lg-12">
                <div class="ud-related-title">
                    <h2 class="ud-related-articles-title">Artikel Lain</h2>
                </div>
            </div>
            <div class="row">
                @foreach ($relatedArticles as $related_article)
                    <div class="col-lg-4 col-md-6">
                        <div class="ud-single-blog">
                            <div class="ud-blog-image">
                                <a href="{{ route('blog.detail', $related_article->id) }}">
                                    <img src="{{ asset('storage/' . $related_article->gambar) }}" alt="blog" />
                                </a>
                            </div>
                            <div class="ud-blog-content">
                                <span class="ud-blog-date">
                                    {{ \Carbon\Carbon::parse($related_article->created_at)->format('d F Y') }}
                                </span>
                                <h3 class="ud-blog-title">
                                    <a href="{{ route('blog.detail', $related_article->id) }}">
                                        {{ $related_article->nama_blog }}
                                    </a>
                                </h3>
                                <p class="ud-blog-desc">
                                    {{ Str::limit($related_article->deskripsi1, 100) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
