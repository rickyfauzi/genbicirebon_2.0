<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Kegiatan;
use App\Models\Anggota;
use App\Models\Komisariat;
use App\Models\Absen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use PDF;

class PembinaController extends Controller
{
    /** index page pembina kegiatan absen */
    public function pembinaRekapKegiatan($id_komisariat)
    {
        $pembinaKeg = Komisariat::where('id_komisariat', $id_komisariat)->first();
        $pembinaKegiatan = Kegiatan::where('id_komisariat', $id_komisariat)->get();
        $sidebar_komis = Komisariat::all();

        return view('pembina.rekap-kegiatan', compact('pembinaKegiatan', 'sidebar_komis', 'pembinaKeg'));
    }

    /** index page pembina rekap */
    public function pembinaRekapAbsen($id_kegiatan)
    {
        $pembinaAb = Kegiatan::where('id_kegiatan', $id_kegiatan)->first();
        $pembinaAbsen = Absen::join('anggotas', 'anggotas.id_anggota', 'absens.id_anggota')->join('kegiatans', 'kegiatans.id_kegiatan', 'absens.id_kegiatan')->where('kegiatans.id_kegiatan', $id_kegiatan)->get();
        $sidebar_komis = Komisariat::all();

        return view('pembina.rekap-absen', compact('pembinaAbsen', 'pembinaAb', 'sidebar_komis'));
    }

    /** view for pembina anggota */
    public function pembinaAnggota()
    {
        $pembinaAng = Anggota::join('komisariats', 'anggotas.id_komisariat', 'komisariats.id_komisariat')->first();
        $pembinaAnggota = Anggota::leftJoin(DB::raw("(SELECT id_anggota, SUM(poin) as total_poin FROM absens GROUP BY id_anggota) as absens_summary"), 'anggotas.id_anggota', 'absens_summary.id_anggota')
            ->join('komisariats', 'anggotas.id_komisariat', 'komisariats.id_komisariat')
            // ->where('anggotas.id_komisariat', $id_komisariat)
            ->select('anggotas.*', 'absens_summary.total_poin', 'komisariats.komisariat')
            ->get();
        // $pembinaAnggota = Anggota::join('komisariats', 'anggotas.id_komisariat', 'komisariats.id_komisariat')->get();
        $sidebar_komis = Komisariat::all();
        return view('pembina.anggota', compact('pembinaAnggota', 'pembinaAng', 'sidebar_komis'));
    }

    /** index page settings */
    public function settingUser()
    {
        $id_user = Auth::user()->id;
        $settingUser = User::where('id', $id_user)->first();
        $sidebar_komis = Komisariat::all();
        return view('setting.setting-user', compact('settingUser', 'sidebar_komis'));
    }

    public function exportKegiatan()
    {

        $data = Kegiatan::all();
        $komisariat = Komisariat::all();
        $pdf = PDF::loadView('pembina.kegiatanrekappdf', compact('data', 'komisariat'), $data->toArray())->output();
        ;
        return response()->streamDownload(
            fn() => print ($pdf),
            "rekap-kegiatan-genbi.pdf"
        );




    }
}