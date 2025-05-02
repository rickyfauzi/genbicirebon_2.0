<?php

namespace App\Http\Controllers;
use App\Models\Blog;
use App\Models\Crudkegiatan;
use App\Models\Korkom;
use App\Models\Komisariat;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        $blogs = Blog::orderBy('tanggal_blog', 'DESC')->limit(3)->get();
        $kegiatans = Crudkegiatan::orderBy('tanggal_kegiatan', 'DESC')->limit(3)->get();

        return view('frontend.index', compact('blogs', 'kegiatans'));
    }

    public function about()
    {
        return view('frontend.about');
    }

    /**
     * Menampilkan halaman pengurus.
     */
    public function organization()
    {
        $korkoms = Korkom::all();
        $topMembers = $korkoms->take(2); // Ambil 2 kartu pertama
        $bottomMembers = $korkoms->slice(2); // Ambil 4 kartu sisanya
        $komisariats = Komisariat::all();
    
        return view('frontend.organization', compact('topMembers', 'bottomMembers', 'komisariats'));
    }

    /**
     * Menampilkan halaman beasiswa.
     */
    public function beasiswa()
    {
        return view('frontend.beasiswa');
    }

    /**
     * Menampilkan halaman detail beasiswa.
     */
    public function beasiswaDetail()
    {
        return view('frontend.beasiswa-detail');
    }

    /**
     * Menampilkan halaman kontak.
     */
    public function contact()
    {
        return view('frontend.contact');
    }

    /**
     * Menampilkan halaman galeri.
     */
    public function galeri()
    {
        return view('frontend.galeri');
    }

    /**
     * Menampilkan halaman muri.
     */
    public function muri()
    {
        return view('frontend.muri');
    }

    /**
     * Menampilkan halaman p-bichamps.
     */
    public function pBichamps()
    {
        return view('frontend.p-bichamps');
    }

    /**
     * Menampilkan halaman program.
     */
    public function kegiatan()
    {
        $kegiatans = Crudkegiatan::all();
        return view('frontend.kegiatan', compact('kegiatans'));
    }

    /**
     * Menampilkan halaman detail program.
     */
    public function kegiatanDetail($id)
    {
        $kegiatan = Crudkegiatan::findOrFail($id);
        return view('frontend.kegiatan-detail', compact('kegiatan'));
    }

    /**
     * Menampilkan halaman blog.
     */
    public function blog()
    {
        $blogs = Blog::all();
        return view('frontend.blog', compact('blogs'));
    }

    /**
     * Menampilkan halaman detail blog.
     */
    public function blogDetail($id)
    {
        $blog = Blog::findOrFail($id);
        $popularArticles = Blog::orderBy('views', 'desc')->take(5)->get();
        $relatedArticles = Blog::where('id', '!=', $id)->take(3)->get();

        return view('frontend.blog-detail', compact('blog', 'popularArticles', 'relatedArticles'));
    }

    /**
     * Menampilkan halaman tentang BI.
     */
    public function tentangBi()
    {
        return view('frontend.tentangbi');
    }

    /**
     * Menampilkan halaman CEF.
     */
    public function cef()
    {
        return view('frontend.cef');
    }

    /**
     * Menampilkan halaman login.
     */
    public function login()
    {
        return view('frontend.login');
    }

    /**
     * Menampilkan halaman persyaratan.
     */
    public function persyaratan()
    {
        return view('frontend.persyaratan');
    }
}
