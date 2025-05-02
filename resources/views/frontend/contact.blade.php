@extends('frontend.layouts.app')
@section('content')
    <section class="ud-page-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ud-banner-bi-content">
                        <h1>Kontak </h1>
                        <ol class="banner-link">
                            <li class="banner-link-nav">
                                <a class="nav-satu" href="index.html">Home</a>
                            </li>

                            <li class="banner-link-nav">
                                <a class="active" href="about.html">kontak</a>
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

    <!-- ====== Contact Start ====== -->
    <section id="contact" class="ud-contact">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-8 col-lg-7">
                    <div class="ud-contact-content-wrapper">
                        <div class="ud-contact-title">
                            <span data-aos="fade-right" data-aos-duration="1000">CONTACT US</span>
                            <h2 data-aos="fade-down" data-aos-duration="1000">
                                Let’s talk about <br />
                                Love to hear from you!
                            </h2>
                        </div>
                        <div class="ud-contact-info-wrapper">
                            <div class="ud-single-info" data-aos="fade-up" data-aos-duration="1000">
                                <div class="ud-info-icon">
                                    <i class="lni lni-map-marker"></i>
                                </div>
                                <div class="ud-info-meta">
                                    <h5>Alamat</h5>
                                    <p>
                                        Jl. Yos Sudarso No.5-7, Lemahwungkuk, Kec. Lemahwungkuk, Kota Cirebon, Jawa Barat
                                        45111</p>
                                </div>
                            </div>
                            <div class="ud-single-info" data-aos="fade-up" data-aos-duration="1000">
                                <div class="ud-info-icon">
                                    <i class="lni lni-envelope"></i>
                                </div>
                                <div class="ud-info-meta">
                                    <h5>Messege</h5>
                                    <p>genbicirebon@gmail.com</p>
                                    <p>genbi.cirebon1411@gmail.com</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-5">
                    <div class="ud-contact-form-wrapper wow fadeInUp" data-wow-delay=".2s" data-aos="fade-up"
                        data-aos-duration="1000">
                        <h3 class="ud-contact-form-title" data-aos="fade-up" data-aos-duration="1000">Kirimkan Pesan</h3>
                        <form class="ud-contact-form">
                            <div class="ud-form-group">
                                <label for="fullName"data-aos="fade-right" data-aos-duration="1000">Nama Lengkap *</label>
                                <input type="text" name="fullName" placeholder="Ricky Ahmad" data-aos="fade-up"
                                    data-aos-duration="1000" />
                            </div>
                            <div class="ud-form-group">
                                <label for="email" data-aos="fade-right" data-aos-duration="1000">Email*</label>
                                <input type="email" name="email" placeholder="example@yourmail.com" data-aos="fade-up"
                                    data-aos-duration="1000" />
                            </div>
                            <div class="ud-form-group">
                                <label for="phone"data-aos="fade-right" data-aos-duration="1000">no.hp*</label>
                                <input type="text" name="phone" placeholder="+62 821 5211 552" data-aos="fade-up"
                                    data-aos-duration="1000" />
                            </div>
                            <div class="ud-form-group">
                                <label for="message"data-aos="fade-right" data-aos-duration="1000">Pesan*</label>
                                <textarea name="message" rows="1" placeholder="type your message here" data-aos="fade-up"
                                    data-aos-duration="1000"></textarea>
                            </div>
                            <div class="ud-form-group mb-0">
                                <button type="submit" class="ud-main-btn">
                                    Kirim pesan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
