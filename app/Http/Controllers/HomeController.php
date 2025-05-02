<?php

namespace App\Http\Controllers;

use App\Models\Kata_Mutiara;
use DB;
use App\Models\User;
use App\Models\Komisariat;
use App\Models\Kegiatan;
use App\Models\Absen;
use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    /** home dashboard */
    public function index()
    {
        $id_komisariat = Auth::user()->id_komisariat;
        $email = Auth::user()->email;
        $id_user = Auth::user()->id;
        $user = User::all();
        $komisariat = Komisariat::join('users', 'komisariats.id_komisariat', 'users.id_komisariat')->where('users.id_komisariat', $id_komisariat)->where('users.email', $email)->first();
        $pembinaKomisariat = Komisariat::select('id_komisariat', 'komisariat')->get();
        $today = Carbon::today()->setTimezone('Asia/Jakarta')->toDateString();
        $kegiatanNow = Kegiatan::where('tgl_pelaksanaan', $today)->get();
        $sidebar_komis = Komisariat::all();
        $kataMutiara = Kata_Mutiara::select('id', 'judul', 'pengarang')->inRandomOrder()->first();
        $poin = Absen::join('anggotas', 'anggotas.id_anggota', 'absens.id_anggota')->where('anggotas.id_user', $id_user)->sum('absens.poin');
        $totalAnggota = Anggota::count();
        $totalKomisariat = Komisariat::count();
        $totalKegiatan = Kegiatan::count();
        $topAnggota = Anggota::leftJoin(DB::raw("(SELECT id_anggota, SUM(poin) as total_poin FROM absens GROUP BY id_anggota) as absens_summary"), 'anggotas.id_anggota', 'absens_summary.id_anggota')
            ->join('komisariats', 'anggotas.id_komisariat', 'komisariats.id_komisariat')
            ->where('anggotas.id_komisariat', $id_komisariat)
            ->orderBy('absens_summary.total_poin', 'desc')
            ->take(5)
            ->select('anggotas.*', 'absens_summary.total_poin')
            ->get();



        return view('dashboard.home', compact('user', 'komisariat', 'pembinaKomisariat', 'kegiatanNow', 'sidebar_komis', 'kataMutiara', 'poin', 'topAnggota'));
    }
}