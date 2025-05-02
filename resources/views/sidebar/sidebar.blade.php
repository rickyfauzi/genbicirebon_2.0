<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>Main Menu</span>
                </li>

                <!-- ANGGOTA SIDEBAR -->
                @if (Session::get('role_name') === 'Anggota' || Session::get('role_name') === 'Anggota')
                    <li class="{{ request()->is('home', 'anggota/absen-anggota/*') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="{{ set_active(['home']) }}"><i class="feather-grid"></i>
                            <span> Dashboard</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('anggota/rekap-absen') ? 'active' : '' }}">
                        <a href="{{ route('anggota/rekap-absen') }}">
                            <i class="fas fa-folder-open"></i>
                            <span>Riwayat Absensi</span>
                        </a>
                    </li>
                @endif

                <!-- SEKUM SIDEBAR -->
                @if (Session::get('role_name') === 'Sekretaris Umum' || Session::get('role_name') === 'Sekretaris Umum')
                    <li class="{{ request()->is('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}"><i class="feather-grid"></i>
                            <span> Dashboard</span>
                        </a>
                    </li>
                    <li
                        class="submenu {{ request()->is('sekum/kegiatan-add', 'sekum/kegiatan-edit/*', 'sekum/kegiatan/genbi', 'sekum/kegiatan/utama', 'sekum/kegiatan/tambahan', 'sekum/anggota-absen', 'sekum/anggota-absen/*', 'sekum/absen-edit/*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-file"></i>
                            <span> Absen</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="{{ route('sekum/kegiatan-add') }}"
                                    class="{{ request()->is('sekum/kegiatan-add') ? 'active' : '' }}">Tambah
                                    Kegiatan</a></li>
                            <li><a href="{{ route('sekum/kegiatan/genbi') }}"
                                    class="{{ request()->is('sekum/kegiatan/genbi') ? 'active' : '' }}">Kegiatan
                                    GenBI Cirebon</a></li>
                            <li><a href="{{ route('sekum/kegiatan/utama') }}"
                                    class="{{ request()->is('sekum/kegiatan/utama') ? 'active' : '' }}">Kegiatan
                                    Utama</a></li>
                            <li><a href="{{ route('sekum/kegiatan/tambahan') }}"
                                    class="{{ request()->is('sekum/kegiatan/tambahan') ? 'active' : '' }}">Kegiatan
                                    Tambahan</a></li>
                        </ul>
                    </li>
                    <li
                        class="submenu {{ request()->is('sekum/rekap-absen', 'sekum/rekap-absen-result') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-folder-open"></i>
                            <span> Rekap</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="{{ route('sekum/rekap-absen') }}"
                                    class="{{ request()->is('sekum/rekap-absen', 'sekum/rekap-absen-result') ? 'active' : '' }}">Rekap
                                    Absen</a></li>
                        </ul>
                    </li>
                    <li class="{{ request()->is('sekum/anggota') ? 'active' : '' }}">
                        <a href="{{ route('sekum/anggota') }}">
                            <i class="fas fa-users"></i>
                            <span>Anggota</span>
                        </a>
                    </li>
                @endif

                <!-- PEMBINA SIDEBAR -->
                @if (Session::get('role_name') === 'Pembina' || Session::get('role_name') === 'Pembina')
                    <li class="{{ request()->is('home') ? 'active' : '' }}">
                        <a href="{{ route('home') }}" class="{{ set_active(['home']) }}"><i class="feather-grid"></i>
                            <span> Dashboard</span>
                        </a>
                    </li>
                    <li
                        class="submenu {{ request()->is('pembina/rekap-kegiatan/*', 'pembina/rekap-absen/*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-folder-open"></i>
                            <span> Rekap</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            @foreach ($sidebar_komis as $key => $list)
                                <li><a href="{{ url('pembina/rekap-kegiatan/id_komisariat=' . $list->id_komisariat) }}"
                                        class="{{ set_active(['pembina/rekap-kegiatan']) }}">{{ $list->komisariat }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    <li class="{{ set_active(['pembina/anggota']) }}">
                        <a href="{{ route('pembina/anggota') }}">
                            <i class="fas fa-users"></i>
                            <span>Anggota</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['setting/setting-user']) }}">
                        <a href="{{ route('setting/setting-user') }}">
                            <i class="fas fa-settings"></i>
                            <span>Setting</span>
                        </a>
                    </li>
                @endif
                <!-- ADMIN SIDEBAR -->
                <!-- ADMIN SIDEBAR -->
                @if (Session::get('role_name') === 'Admin' || Session::get('role_name') === 'Admin')
                    <li class="{{ set_active(['home']) }}">
                        <a href="{{ route('home') }}" class="{{ set_active(['home']) }}"><i class="feather-grid"></i>
                            <span> Dashboard</span>
                        </a>
                    </li>
                    <li
                        class="submenu {{ set_active(['admin/user']) }} {{ request()->is('student/edit/*') ? 'active' : '' }} {{ request()->is('student/profile/*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-users"></i>
                            <span> Users Manegement</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <li><a href="{{ url('admin/user/anggota') }}"
                                    class="{{ set_active(['admin/user']) }}">User Akun</a></li>
                            <li><a href="{{ url('admin/user/setanggota') }}"
                                    class="{{ set_active(['admin/user']) }}">Anggota</a></li>
                        </ul>
                    </li>
                    <li class="{{ set_active(['admin/kegiatan']) }}">
                        <a href="{{ route('admin/kegiatan') }}">
                            <i class="fas fa-calendar"></i>
                            <span>Kegiatan</span>
                        </a>
                    </li>

                    <li class="{{ set_active(['admin.k-kegiatan']) }}">
                        <a href="{{ route('admin.k-kegiatan') }}"><i class="fas fa-calendar-check"></i>
                            <span>Kelola Kegiatan</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['admin.k-blog']) }}">
                        <a href="{{ route('admin.k-blog') }}">
                            <i class="fas fa-newspaper"></i>
                            <span>Kelola Blog</span>
                        </a>
                    </li>
                    {{-- <li class="{{ set_active(['admin.comment']) }}">
                        <a href="{{ route('admin.comment') }}"><i class="fas fa-comment"></i>
                            <span>Kelola Komentar</span>
                        </a>
                    </li> --}}
                    <li
                        class="submenu {{ set_active(['admin/korkom']) }} {{ request()->is('student/edit/*') ? 'active' : '' }} {{ request()->is('student/profile/*') ? 'active' : '' }}">
                        <a href="#"><i class="fas fa-users"></i>
                            <span> kelola pengurus</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <ul>
                            <!--<li><a href="#" class="{{ set_active(['admin/user']) }}">Pembina</a></li>-->
                            <li><a href="{{ url('admin/k-korkom') }}"
                                    class="{{ set_active(['admin.k-korkom']) }}">Korkom</a></li>
                            <li><a href="{{ url('admin/k-komis') }}"
                                    class="{{ set_active(['admin.k-komis']) }}">Komisariat</a></li>
                        </ul>
                    </li>
                @endif


            </ul>
        </div>
    </div>
</div>
