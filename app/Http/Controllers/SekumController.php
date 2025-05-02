<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Kegiatan;
use App\Models\Anggota;
use App\Models\Komisariat;
use App\Models\User;
use App\Models\Absen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;


class SekumController extends Controller
{
    /** index page sekum kegiatan genbi */
    public function sekumKegiatanGenbi()
    {
        $id_komisariat = Auth::user()->id_komisariat;
        $sekumKegiatanGenbi = Kegiatan::where('jenis', 'Utama')->where('id_komisariat', '1')->get();
        return view('sekum.kegiatan', compact('sekumKegiatanGenbi', 'id_komisariat'));
    }

    /** index page sekum kegiatan utama */
    public function sekumKegiatanUtama()
    {
        $id_komisariat = Auth::user()->id_komisariat;
        $sekumKegiatanUtama = Kegiatan::where('jenis', 'Utama')->where('id_komisariat', $id_komisariat)->get();
        return view('sekum.kegiatan', compact('sekumKegiatanUtama'));
    }

    /** index page sekum kegiatan tambahan */
    public function sekumKegiatanTambahan()
    {
        $id_komisariat = Auth::user()->id_komisariat;
        $sekumKegiatanTambahan = Kegiatan::where('jenis', 'Tambahan')->where('id_komisariat', $id_komisariat)->get();
        return view('sekum.kegiatan', compact('sekumKegiatanTambahan'));
    }

    /** index page sekum rekap */
  /** index page sekum rekap */
public function sekumRekap(Request $request)
{
    $id_komisariat = Auth::user()->id_komisariat;

    // Menampilkan kegiatan yang sudah dilaksanakan berdasarkan id_komisariat dan tgl_pelaksanaan yang sudah lewat
    $sekumKegiatan = Kegiatan::where('id_komisariat', $id_komisariat)
                              ->where('tgl_pelaksanaan', '<=', Carbon::now()) // Hanya yang sudah dilaksanakan
                              ->orderBy('tgl_pelaksanaan', 'DESC')
                              ->get();

    if ($request->has('id_kegiatan')) {
        $cari = $request->id_kegiatan;
        // Mengambil data absen berdasarkan id_kegiatan dan id_komisariat
        $sekumAbsen = Anggota::join('absens', 'absens.id_anggota', 'anggotas.id_anggota')
                             ->where('absens.id_kegiatan', $cari)
                             ->where('anggotas.id_komisariat', $id_komisariat)
                             ->get();
        $sekumAbsenKeg = Kegiatan::where('id_kegiatan', $cari)->first();
    } else {
        // Jika id_kegiatan belum diset, tampilkan rekap kosong
        $sekumAbsen = [];
        $sekumAbsenKeg = "Rekap";
    }

    return view('sekum.rekap-absen', compact('sekumAbsen', 'sekumKegiatan', 'sekumAbsenKeg'));
}


    /** sekum add page */
    public function sekumKegiatanAdd()
    {
        $id_komisariat = Auth::user()->id_komisariat;
        $today = Carbon::now()->format('Y-m-d H:i:s');
        $id_anggota = Anggota::where('id_komisariat', $id_komisariat)->where('status', 'aktif')->get();
        return view('sekum.kegiatan-add', compact('id_komisariat', 'today', 'id_anggota'));
    }

    /** sekum kegiatan save record */
    public function sekumKegiatanSave(Request $request)
    {
        try {
            $request->validate([
                'nama_kegiatan' => 'required|string',
                'tgl_pelaksanaan' => 'required|date',
                'jenis' => 'required|string',
                'id_komisariat' => 'required',
                'today' => 'required',
            ]);

            $kegiatan_id = Kegiatan::create([
                'nama_kegiatan' => $request->nama_kegiatan,
                'tgl_pelaksanaan' => $request->tgl_pelaksanaan,
                'poin' => '10',
                'jenis' => $request->jenis,
                'id_komisariat' => $request->id_komisariat,
                'created_at' => $request->today,
                'updated_at' => $request->today,
            ])->id;
            //method create kalo di dd otomatis return data yang disimpen, beserta id nya

            $id_komisariat = Auth::user()->id_komisariat;
            $anggotaId = DB::table('anggotas')->select('id_anggota')->where('id_komisariat', $id_komisariat)->where('status', 'Active')->get();
            // dd($anggotaId);
            foreach ($anggotaId as $key => $id) {
                $answers[] = [
                    'id_kegiatan' => $kegiatan_id,
                    'id_anggota' => $id->id_anggota,
                    'keterangan' => 'Alpa',
                    'poin' => '0',
                    'updated_at' => $request->today, // ini bisa pake date("Y-m-d")
                ];
            }
            DB::table("absens")->insert($answers);

            Toastr::success('Data berhasil disimpan', 'Success');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollback();
            Toastr::error('Ups ada kesalahan, silahkan coba lagi!', 'Error');
            return redirect()->back();
        }
    }

    /** view for edit kegiatan */
    public function sekumKegiatanEdit($id_kegiatan)
    {
        $id_komisariat = Auth::user()->id_komisariat;
        $today = Carbon::now()->format('Y-m-d H:i:s');
        $sekumKegiatanEdit = Kegiatan::where('id_kegiatan', $id_kegiatan)->first();
        return view('sekum.kegiatan-edit', compact('sekumKegiatanEdit', 'id_komisariat', 'today'));
    }

    /** update record */
    public function sekumKegiatanUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            $updateRecord = [
                'nama_kegiatan' => $request->nama_kegiatan,
                'tgl_pelaksanaan' => $request->tgl_pelaksanaan,
                'jenis' => $request->jenis,
                'id_komisariat' => $request->id_komisariat,
                'updated_at' => $request->updated_at,
            ];
            Kegiatan::where('id_kegiatan', $request->id_kegiatan)->update($updateRecord);

            Toastr::success('Data berhasil diupdate', 'Success');
            DB::commit();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Ups ada kesalahan, silahkan coba lagi!', 'Error');
            return redirect()->back();
        }
    }

    /** sekum delete */
    public function sekumDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            // dd($request->id_kegiatan);
            if (!empty($request->id_kegiatan)) {
                Kegiatan::where('id_kegiatan', $request->id_kegiatan)->delete();
                DB::commit();
                Toastr::success('Data berhasil dihapus', 'Success');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Ups ada kesalahan, silahkan coba lagi!', 'Error');
            return redirect()->back();
        }
    }

    /** view for sekum anggota */
    public function sekumAnggota()
    {
        $id_komisariat = Auth::user()->id_komisariat;
        $email = Auth::user()->email;
        $komisariat = Komisariat::join('users', 'komisariats.id_komisariat', 'users.id_komisariat')->where('users.id_komisariat', $id_komisariat)->where('users.email', $email)->first();
        $sekumAnggota = Anggota::leftJoin(DB::raw("(SELECT id_anggota, SUM(poin) as total_poin FROM absens GROUP BY id_anggota) as absens_summary"), 'anggotas.id_anggota', 'absens_summary.id_anggota')
            ->join('komisariats', 'anggotas.id_komisariat', 'komisariats.id_komisariat')
            ->where('anggotas.id_komisariat', $id_komisariat)
            ->select('anggotas.*', 'absens_summary.total_poin')
            ->get();
        return view('sekum.anggota', compact('sekumAnggota', 'komisariat'));
    }

    /** view for sekum anggota absen */
    public function sekumAnggotaAbsen($id_kegiatan, $id_komisariat)
    {
        $sekumAnggotaAbsen = Anggota::join('absens', 'anggotas.id_anggota', 'absens.id_anggota')->join('komisariats', 'anggotas.id_komisariat', 'komisariats.id_komisariat')->where('absens.id_kegiatan', $id_kegiatan)->where('anggotas.id_komisariat', $id_komisariat)->get();
        $sekumKegiatan = Kegiatan::where('id_kegiatan', $id_kegiatan)->first();
        return view('sekum.anggota-absen', compact('sekumAnggotaAbsen', 'sekumKegiatan'));
    }

    /** view for edit absen */
    public function sekumAbsenEdit($id_absen)
    {
        $sekumAbsenEdit = Absen::join('anggotas', 'anggotas.id_anggota', 'absens.id_anggota')->join('kegiatans', 'kegiatans.id_kegiatan', 'absens.id_kegiatan')->where('absens.id_absen', $id_absen)->first();
        return view('sekum.absen-edit', compact('sekumAbsenEdit'));
    }

    /** sekum kegiatan save record */
    public function sekumAbsenUpdate(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->keterangan == 'Hadir') {
                $poin = '10';
            } else {
                $poin = '0';
            }
            $updateRecord = [
                'poin' => $poin,
                'keterangan' => $request->keterangan,
            ];
            Absen::where('id_absen', $request->id_absen)->update($updateRecord);

            Toastr::success('Data berhasil disimpan', 'Success');
            DB::commit();
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('Ups ada kesalahan, silahkan coba lagi!', 'Error');
            return redirect()->back();
        }
    }

    /** index page settings */
    public function settingUser()
    {
        $id_user = Auth::user()->id;
        $settingsUser = User::where('id', $id_user)->first();
        $sidebar_komis = Komisariat::all();
        return view('setting.setting-user', compact('settingUser', 'sidebar_komis'));
    }
}