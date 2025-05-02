<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Kegiatan;
use App\Models\Anggota;
use App\Models\Komisariat;
use App\Models\User;
use App\Models\Absen;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AnggotaController extends Controller
{
    /** index page anggota kegiatan */
    public function anggotaKegiatan()
    {
        $anggotaKegiatan = Kegiatan::all();
        return view('anggota.absen-kegiatan',compact('anggotaKegiatan'));
    }

    /** index page anggota absen */
    public function anggotaAbsen($id_kegiatan)
    {
        $id_user        = Auth::user()->id;
        $anggotaAbsen   = Absen::join('kegiatans', 'kegiatans.id_kegiatan', 'absens.id_kegiatan')
        ->join('anggotas', 'anggotas.id_anggota', 'absens.id_anggota')
        ->where('kegiatans.id_kegiatan', $id_kegiatan)
        ->where('anggotas.id_user', $id_user)
        ->first();    
        $komisariat = Kegiatan::join('komisariats', 'komisariats.id_komisariat', 'kegiatans.id_komisariat')
        ->where('kegiatans.id_kegiatan', $id_kegiatan)
        ->first();
        return view('anggota.absen-anggota',compact('anggotaAbsen', 'komisariat'));
    }

    /** index page anggota rekap kegiatan */
    public function anggotaRekap()
    {
        $id_user        = Auth::user()->id;
        $id_anggota     = Anggota::where('id_user', $id_user)->first();
        $anggotaRekap   = Absen::join('kegiatans', 'kegiatans.id_kegiatan', 'absens.id_kegiatan')->where('absens.id_anggota', $id_anggota->id_anggota)->get();
        // dd($anggotaRekap);
        return view('anggota.rekap-absen',compact('anggotaRekap'));
    }

    /** update record */
    public function anggotaUpdate(Request $request)
    {        
        DB::beginTransaction();
        try {    
            if($request->keterangan == 'Hadir'){
                $poin    = '10';
            } else {
                $poin    = '0';
            }
            $updateRecord = [
                'poin'          => $poin,
                'keterangan'    => $request->keterangan,
            ];
            
            // $a = $request->id_absen;
            Absen::where('id_absen', $request->id_absen)->update($updateRecord);
            // dd($a);
            Toastr::success('Alhamdulillah Sudah Absen','Success');
            DB::commit();
            return redirect()->back();
        
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('Ups ada kesalahan, silahkan coba lagi!','Error');
            return redirect()->back();
        }
    }

    /** index page settings */
    public function settingUser()
    {
        $id_user        = Auth::user()->id;
        $settingUser    = User::where('id', $id_user)->first();
        $sidebar_komis  = Komisariat::all();
        $originalValue  = Auth::user()->password;
        // $encryptedValue = decodeURIComponent($originalValue);

        return view('setting.setting-user', compact('settingUser', 'sidebar_komis'));
    }    

    public function settingUserUpdate(Request $request)
    {
    
      // Validate the request data
    $validatedData = $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8',
    ]);

    // Hash the password
    $validatedData['password'] = Hash::make($validatedData['password']);

    // Get the authenticated user
    $user = auth()->user();

    // Update the user's attributes
    $user->update($validatedData);

    // Optionally, you might want to redirect to the user settings page
    Toastr::success('Update Akun tersimpan :)','Success');
    return redirect()->route('setting/setting-user');
    }
}
