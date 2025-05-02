<header class="ud-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <nav class="navbar navbar-expand-lg">
                    <a class="logo" href="{{ route('home') }}">
                        <img src="{{ asset('assets2/images/genbibg.png') }}" alt="Logo" class="logo" />                        
                    </a>
                    <button class="navbar-toggler">
                        <span class="toggler-icon"> </span>
                        <span class="toggler-icon"> </span>
                        <span class="toggler-icon"> </span>
                    </button>

                    <div class="navbar-collapse">
                        <ul id="nav" class="navbar-nav mx-auto">
                            <li class="nav-item active">
                                <a class="nav-link" href="{{ route('index') }}">Beranda</a>
                            </li>
                            <li class="nav-item nav-item-has-children">
                                <a href="javascript:void(0)"> Tentang </a>
                                <ul class="ud-submenu">
                                    <li class="ud-submenu-item">
                                        <a href="{{ route('about') }}" class="ud-submenu-link">
                                            Tentang GenBI
                                        </a>
                                    </li>
                                    <li class="ud-submenu-item">
                                        <a href="{{ route('organization') }}" class="ud-submenu-link">
                                            Pengurus GenBI Cirebon
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="ud-submenu-link" href="{{ route('kegiatan') }}">Kegiatan</a>
                            </li>
                            <li class="nav-item nav-item-has-children">
                                <a href="javascript:void(0)"> Beasiswa BI </a>
                                <ul class="ud-submenu">
                                    <li class="ud-submenu-item">
                                        <a href="{{ route('tentang-bi') }}" class="ud-submenu-link">
                                            Tentang BI
                                        </a>
                                    </li>
                                    <li class="ud-submenu-item">
                                        <a href="{{ route('persyaratan') }}" class="ud-submenu-link">
                                            Persyaratan Beasiswa
                                        </a>
                                    </li>
                                    <li class="ud-submenu-item">
                                        <a href="{{ route('beasiswa') }}" class="ud-submenu-link">
                                            Beasiswa
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('blog') }}">Berita</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('contact') }}">Hubungi</a>
                            </li>
                            <!-- Elemen placeholder untuk tombol "Masuk" di tampilan mobile -->
                            <li class="nav-item mobile-login d-sm-inline-block d-lg-none">
                                <!-- Tempat tombol "Masuk" akan dipindahkan -->
                            </li>
                        </ul>
                    </div>

                    <!-- Tombol "Masuk" pada tampilan dekstop (default) -->
                    <div class="navbar-btn d-none d-lg-inline-block nav-item desktop-login">
                        <a class="ud-main-btn ud-white-btn" href="{{ route('login')}}">
                            Masuk
                        </a>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>
