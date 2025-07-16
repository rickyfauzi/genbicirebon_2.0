<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\TypeFormController;
use App\Http\Controllers\Setting;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\SekumController;
use App\Http\Controllers\PembinaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Kreait\Firebase\Factory;
use App\Http\Controllers\WebhookController;

// use Illuminate\Support\Facades\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('/chatbot', [ChatbotController::class, 'handleChat']);
Route::get('/test-dialogflow', function () {
    try {
        putenv("GOOGLE_APPLICATION_CREDENTIALS=" . config('services.dialogflow.credentials'));
        $client = new \Google\Cloud\Dialogflow\V2\SessionsClient();
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

/** for side bar menu active */
function set_active($route)
{
    if (is_array($route)) {
        return (Request($route)) ? 'active' : '';
    }
    return Request::path() == $route ? 'active' : '';
}



Route::get('/', [FrontendController::class, 'index'])->name('index');

Route::group(['middleware' => 'auth'], function () {
    Route::get('home', function () {
        return view('home');
    });
    Route::get('home', function () {
        return view('home');
    });
});


Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/organization', [FrontendController::class, 'organization'])->name('organization');
Route::get('/beasiswa', [FrontendController::class, 'beasiswa'])->name('beasiswa');
Route::get('/beasiswa-detail', [FrontendController::class, 'beasiswaDetail'])->name('beasiswa.detail');
Route::get('/contact', [FrontendController::class, 'contact'])->name('contact');
Route::get('/galeri', [FrontendController::class, 'galeri'])->name('galeri');
Route::get('/muri', [FrontendController::class, 'muri'])->name('muri');
Route::get('/p-bichamps', [FrontendController::class, 'pBichamps'])->name('p-bichamps');
Route::get('/kegiatan', [FrontendController::class, 'kegiatan'])->name('kegiatan');
Route::get('/kegiatan/{id}', [FrontendController::class, 'kegiatanDetail'])->name('kegiatan.detail');
Route::get('/blog', [FrontendController::class, 'blog'])->name('blog');
Route::get('/blog/{id}', [FrontendController::class, 'blogDetail'])->name('blog.detail');
Route::get('/tentang-bi', [FrontendController::class, 'tentangBi'])->name('tentang-bi');
Route::get('/cef', [FrontendController::class, 'cef'])->name('cef');
Route::get('/login', [FrontendController::class, 'login'])->name('login');
Route::get('/persyaratan', [FrontendController::class, 'persyaratan'])->name('persyaratan');



// ----------------------------login ------------------------------//
Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'authenticate');
    Route::get('/logout', 'logout')->name('logout');
    Route::post('change/password', 'changePassword')->name('change/password');
});
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );
    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

// -------------------------- main dashboard ----------------------//
Route::controller(HomeController::class)->group(function () {
    Route::get('/home', 'index')->middleware('auth')->name('home');
});

// ------------------------ setting -------------------------------//
// Route::controller(Setting::class)->group(function () {
//     Route::get('setting/page', 'index')->middleware('auth')->name('setting/page');
// });

// ------------------------ sekretaris umum -------------------------------//
Route::controller(SekumController::class)->group(function () {
    Route::get('sekum/kegiatan/genbi', 'sekumKegiatanGenbi')->middleware('auth')->name('sekum/kegiatan/genbi'); // tampilan kegiatan genbi
    Route::get('sekum/kegiatan/utama', 'sekumKegiatanUtama')->middleware('auth')->name('sekum/kegiatan/utama'); // tampilan kegiatan utama
    Route::get('sekum/kegiatan/tambahan', 'sekumKegiatanTambahan')->middleware('auth')->name('sekum/kegiatan/tambahan'); // tampilan kegiatan tambahan
    Route::get('sekum/kegiatan-add', 'sekumKegiatanAdd')->middleware('auth')->name('sekum/kegiatan-add'); // tambah kegiatan
    Route::post('sekum/kegiatan/save', 'sekumKegiatanSave')->middleware('auth')->name('sekum/kegiatan/save'); // simpan kegiatan
    Route::post('sekum/delete', 'sekumDelete')->middleware('auth')->name('sekum/delete'); // delete kegiatan
    Route::get('sekum/kegiatan-edit/{id_kegiatan}', 'sekumKegiatanEdit')->middleware('auth')->name('sekum/kegiatan-edit'); // edit kegiatan
    Route::post('sekum/kegiatan/update', 'sekumKegiatanUpdate')->middleware('auth')->name('sekum/kegiatan/update'); // update kegiatan
    Route::get('sekum/anggota', 'sekumAnggota')->middleware('auth')->name('sekum/anggota'); // tampilan anggota
    Route::get('sekum/anggota-absen/id_kegiatan={id_kegiatan}&&id_komisariat={id_komisariat}', 'sekumAnggotaAbsen')->middleware('auth')->name('sekum/anggota-absen'); // tambah absen anggota
    Route::get('sekum/absen-edit/id_absen={id_absen}', 'sekumAbsenEdit')->middleware('auth')->name('sekum/absen-edit'); // edit absen anggota
    Route::post('sekum/absen/update', 'sekumAbsenUpdate')->middleware('auth')->name('sekum/absen/update'); // update kegiatan
    Route::get('sekum/rekap-absen', 'sekumRekap')->middleware('auth')->name('sekum/rekap-absen'); // rekap absen
    Route::get('sekum/rekap-absen-result', 'sekumRekap')->middleware('auth')->name('rekap-absen-result'); // rekap absen
    Route::get('export/export-absen-rekap', 'exportRekap')->middleware('auth')->name('export/export-absen-rekap'); // export rekap absen
    Route::get('setting/setting-user', 'settingUser')->middleware('auth')->name('setting/setting-user'); // setting user
});

// ------------------------ pembina -------------------------------//
Route::controller(PembinaController::class)->group(function () {
    Route::get('pembina/anggota', 'pembinaAnggota')->middleware('auth')->name('pembina/anggota'); // tampilan absen komis
    Route::get('pembina/rekap-kegiatan/id_komisariat={id_komisariat}', 'pembinaRekapKegiatan')->middleware('auth')->name('pembina/rekap-kegiatan'); // rekap absen
    Route::get('pembina/rekap-absen/id_kegiatan={id_kegiatan}', 'pembinaRekapAbsen')->middleware('auth')->name('pembina/rekap-absen'); // rekap absen
    Route::get('export/export-absen-rekap', 'exportRekap')->middleware('auth')->name('export/export-absen-rekap'); // export rekap absen
    Route::get('export/kegiatan', 'exportKegiatan')->middleware('auth')->name('export/kegiatan'); // export rekap absen
    Route::get('setting/setting-user', 'settingUser')->middleware('auth')->name('setting/setting-user'); // setting user
});

// ------------------------ anggota -------------------------------//
Route::controller(AnggotaController::class)->group(function () {
    Route::get('anggota/absen-anggota/id_kegiatan={id_kegiatan}', 'anggotaAbsen')->middleware('auth')->name('anggota/absen-anggota'); // tampilan absen
    Route::get('anggota/absen-anggota', 'anggotaAbsen')->middleware('auth')->name('anggota/absen-anggota'); // tampilan absen
    Route::post('anggota/absen/update', 'anggotaUpdate')->middleware('auth')->name('anggota/absen/update'); // edit absen
    Route::get('anggota/rekap-absen', 'anggotaRekap')->middleware('auth')->name('anggota/rekap-absen'); // rekap absen
    Route::get('setting/setting-user', 'settingUser')->middleware('auth')->name('setting/setting-user'); // setting user
    Route::post('setting/update-user', 'settingUserUpdate')->middleware('auth')->name('setting/update-user'); // setting user
});

// ----------------------- admin -----------------------------//
Route::controller(AdminController::class)->group(function () {
    Route::get('admin/user/anggota', 'adminUser')->middleware('auth')->name('admin/user/anggota'); // tampilan user
    Route::get('admin/user/setanggota', 'adminSetAnggota')->middleware('auth')->name('admin/user/setanggota'); // tampilan user
    Route::get('admin/user-add', 'adminAddUser')->middleware('auth')->name('admin/user-add'); // tambah user
    Route::get('admin/add-anggota', 'AddAnggota')->middleware('auth')->name('admin/add-anggota'); // tambah user
    Route::post('admin/user/saveanggota', 'anggotaSave')->middleware('auth')->name('admin/user/saveanggota'); // simpan akun
    Route::post('admin/user/save', 'userSave')->middleware('auth')->name('admin/user/save'); // simpan akun
    Route::post('admin/delete', 'adminDelete')->middleware('auth')->name('admin/delete'); // delete akun
    Route::get('admin/kegiatan', 'adminKegiatan')->middleware('auth')->name('admin/kegiatan'); //tampilan page kegiatan di admin
    Route::get('admin/anggota-edit/id_anggota={id_anggota}', 'adminAnggotaEdit')->middleware('auth')->name('admin/anggota-edit'); // edit absen anggota
    Route::post('admin/anggota/update', 'anggotaUpdate')->middleware('auth')->name('admin/anggota/update'); // simpan akun
    Route::get('admin/galery', 'adminGalery')->middleware('auth')->name('admin/galery'); //tampilan page kegiatan di admin
    Route::post('admin/galery/tambah', 'tambahGalery')->middleware('auth')->name('admin/galery/tambah');
    Route::get('admin/galery/create', 'adminGaleryCreate')->middleware('auth')->name('admin/galery/create'); //tampilan page kegiatan di admin
    Route::get('/admin/home', [AdminController::class, 'home'])->name('admin.home');
    //excel users
    Route::get('admin/import', 'pageImport')->middleware('auth')->name('admin/import'); //tampilan page user di admin
    Route::post('admin/user-import', 'import')->middleware('auth')->name('admin/user-import');
    Route::get('admin/user-export', 'export')->middleware('auth')->name('admin/user-export'); //export
    Route::post('admin/import-anggota', 'pageImportAnggota')->middleware('auth')->name('admin/import-anggota');



    //excel  anggota
    Route::get('admin/import-anggota', 'pageImportAnggota')->middleware('auth')->name('admin/import-anggota'); //tampilan page anggota di admin
    Route::post('admin/anggota-import', 'import_anggota')->middleware('auth')->name('admin/anggota-import'); //import
    Route::get('admin/anggota-export', 'export_anggota')->middleware('auth')->name('admin/anggota-export'); //export

    Route::get('admin/k-kegiatan', 'kelolaKegiatan')->name('admin.k-kegiatan');
    Route::get('admin/kegiatan/create', 'KegiatanCreate')->name('admin.kegiatan-create');
    Route::post('admin/kegiatan/store', 'storeKegiatan')->name('admin.kegiatan.store');
    Route::get('admin/kegiatan/edit/{id}', 'editKegiatan')->name('admin.kegiatan.edit');
    Route::post('admin/kegiatan/edit/{id}', 'editKegiatan')->name('admin.kegiatan.edit');
    Route::post('admin/kegiatan/update/{id}', 'updateKegiatan')->name('admin.kegiatan.update');
    Route::put('admin/kegiatan/update/{id}',  'updateKegiatan')->name('admin.kegiatan.update');
    Route::delete('admin/kegiatan/delete/{id}', 'destroyKegiatan')->name('admin.kegiatan.delete');
    // blog
    Route::get('admin/k-blog', 'kelolaBlog')->middleware('auth')->name('admin.k-blog');
    Route::get('admin/blog/create', 'create')->middleware('auth')->name('admin.blog-create');
    Route::post('admin/blog/store', 'store')->middleware('auth')->name('admin.blog.store');
    Route::get('admin/blog/edit/{id}', 'edit')->middleware('auth')->name('admin.blog-edit');
    Route::put('admin/blog/update/{id}', 'update')->middleware('auth')->name('admin.blog.update');
    Route::delete('admin/blog/delete/{id}', 'destroy')->middleware('auth')->name('admin.blog.delete');

    Route::get('admin/k-korkom', 'kelolaKorkom')->name('admin.k-korkom');
    Route::get('admin/korkom-create', 'createKorkom')->middleware('auth')->name('admin.korkom-create');
    Route::post('admin.korkom.storeKorkom', 'storeKorkom')->middleware('auth')->name('admin.korkom.storeKorkom');
    Route::get('admin.korkom.edit/{id}', 'editKorkom')->middleware('auth')->name('admin.korkom-edit');
    Route::put('admin/korkom/update/{id}', 'updateKorkom')->middleware('auth')->name('admin.korkom.update');
    Route::post('admin/korkom/update/{id}', 'updateKorkom')->middleware('auth')->name('admin.korkom.update');
    Route::delete('admin/korkom/delete/{id}', 'deleteKorkom')->middleware('auth')->name('admin.korkom.delete');
    //komis
    Route::get('admin/k-komis', 'kelolaKomisariat')->name('admin.k-komis');
    Route::get('admin/comment', 'indexComments')->name('admin.comment');
    Route::get('admin/komis-create', 'createKomisariat')->middleware('auth')->name('admin.komis-create');
    Route::post('admin.komisariat.storeKomisariat', 'storeKomisariat')->middleware('auth')->name('admin.komisariat.storeKomisariat');
    Route::get('admin.komisariat.edit/{id}', 'editKomisariat')->middleware('auth')->name('admin.komis-edit');
    Route::put('admin/komisariat/update/{id}', 'updateKomisariat')->middleware('auth')->name('admin.komisariat.update');
    Route::post('admin/komisariat/update/{id}', 'updateKomisariat')->middleware('auth')->name('admin.komisariat.update');
    Route::delete('admin/komisariat/delete/{id}', 'deleteKomisariat')->middleware('auth')->name('admin.komisariat.delete');
    Route::delete('/admin/komis/delete/{id_komisariat}', 'deleteKomisariat')->middleware('auth')->name('admin.komisariat.delete');
    Route::get('/admin/comment', 'indexComments')->name('admin.comment');
    Route::post('/admin/comments/{id}/approve', 'approveComment')->name('admin.comments.approve');
    Route::delete('/admin/comments/{id}', 'deleteComment')->name('admin.comments.delete');
    Route::post('/admin/comments/{id}/reject', 'reject')->name('admin.comments.reject');



    //     Route::get('admin/comment', [AdminController::class, 'indexComments'])->name('admin.comment');
    // Route::post('/admin/comments/{id}/approve', [AdminController::class, 'approveComment'])->name('admin.comments.approve');
    // Route::delete('/admin/comments/{id}', [AdminController::class, 'deleteComment'])->name('admin.comments.delete');



});
