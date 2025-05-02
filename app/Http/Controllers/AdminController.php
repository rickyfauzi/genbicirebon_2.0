<?php

namespace App\Http\Controllers;

use App\Imports\UserImport;
use App\Imports\AnggotaImport;
use DB;
use App\Models\Kegiatan;
use App\Models\Anggota;
use App\Models\Komisariat;
use App\Models\User;
use App\Models\Absen;
use App\Models\Crudkegiatan;
use App\Models\Galery;
use App\Models\PostComment;
use App\Models\modelKegiatan;
use App\Models\Blog;
use app\Models\Korkom;
use App\Models\Korkom as ModelsKorkom;
use app\Models\Korkom_model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    /** index page admin user */
    public function adminUser()
    {
        $adminUser  = User::join('komisariats', 'komisariats.id_komisariat', 'users.id_komisariat')->get();
        return view('admin.user', compact('adminUser'));
    }

    public function adminAddUser()
    {
        $role = DB::table('role_type_users')->get();
        $komisariat = DB::table('komisariats')->get();
        $addUser = User::join('komisariats', 'komisariats.id_komisariat', 'users.id_komisariat')->get();
        return view('admin.addUser', compact('addUser', 'role', 'komisariat'));
    }

    /** index page sekum kegiatan utama */
    public function adminKegiatan()
    {
        $id_komisariat     = Auth::user()->id_komisariat;
        $adminKegiatan     = Kegiatan::join('komisariats', 'komisariats.id_komisariat', 'kegiatans.id_komisariat')->get();
        return view('admin.kegiatan', compact('adminKegiatan'));
    }
    public function dashboard()
    {
        // Menghitung total anggota dari tabel 'anggotas'
        $totalAnggota = DB::table('anggotas')->count();

        // Menghitung total komisariat dari tabel 'komisariat' (jika ada)
        $totalKomisariat = DB::table('komisariat')->count();

        // Mengirim data ke view dashboard
        return view('dashboard.home', compact('totalAnggota', 'totalKomisariat'));
    }



    public function adminSetAnggota()
    {
        $anggotas = DB::table('anggotas')->get();
        $komisariat = DB::table('komisariats')->get();
        $adminSetAnggota  = Anggota::join('komisariats', 'komisariats.id_komisariat', 'anggotas.id_komisariat')->get();
        return view('admin.setanggota', compact('adminSetAnggota', 'anggotas'));
    }

public function AddAnggota()
{
    $role = DB::table('role_type_users')->get();
    $komisariat = DB::table('komisariats')->get();
    $Anggota = DB::table('users')->get();
    $AddAnggota = User::join('komisariats', 'komisariats.id_komisariat', 'users.id_komisariat')->get();

    // Ensure the view name matches the file's name
    return view('admin.addAnggota', compact('AddAnggota', 'role', 'komisariat', 'Anggota'));
}


    public function userSave(Request $request)
    {
        $validatedData = $request->validate([
            'id_komisariat' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'role_name' => 'required',
            'password' => 'required|min:6',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        User::create($validatedData);

        Toastr::success('User Akun add successfully :)', 'Success');
        return redirect('admin/user/anggota');
    }


    public function adminAnggotaEdit($id_anggota)
    {
        $anggotas = Anggota::where('id_anggota', $id_anggota)->first();
        $anggotass = Anggota::join('komisariats', 'anggotas.id_komisariat', 'komisariats.id_komisariat')->where('id_anggota', $id_anggota)->get();
        $komisariat = Komisariat::all();
        $Anggota  = Anggota::join('komisariats', 'komisariats.id_komisariat', 'anggotas.id_komisariat')->get();
        return view('admin.anggota-edit', compact('Anggota', 'anggotas', 'komisariat', 'anggotass'));
    }

    public function anggotaUpdate(Request $request)
    {

        DB::beginTransaction();
        try {
            $updateRecord = [
                'nama'     => $request->nama,
                'prodi'   => $request->prodi,
                'jk'             => $request->jk,
                'status'     => $request->status,
                'id_komisariat'     => $request->id_komisariat,
                'id_user'     => $request->id_user,
                'updated_at'        => $request->updated_at,
            ];
            Anggota::where('id_anggota', $request->id_anggota)->update($updateRecord);


            Toastr::success('Has been update successfully :)', 'Success');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('fail, update Data Anggota  :)', 'Error');
            return redirect()->back();
        }
    }

    public function AnggotaSave(Request $request)
    {
        $validatedData = $request->validate([

            'nama' => 'required',
            'prodi' => 'required',
            'jk' => 'required',
            'status' => 'required',
            'id_komisariat' => 'required',
            'id_user' => 'required',

        ]);

        Anggota::create($validatedData);

        Toastr::success('Add Anggota successfully :)', 'Success');
        return redirect('admin/user/setanggota');
    }

    /** index page settings */
    public function settingsUser()
    {
        $id_user        = Auth::user()->id;
        $settingsUser   = User::where('id', $id_user)->first();
        return view('admin.settings-user', compact('settingsUser'));
    }

    /** admin user delete */
    public function adminDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            // dd($request->id);
            if (!empty($request->id)) {
                User::where('id', $request->id)->delete();
                DB::commit();
                Toastr::success('User Account deleted successfully :)', 'Success');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('User Account deleted fail :)', 'Error');
            return redirect()->back();
        }
    }

    //galery

    public function tambahGalery(Request $request)
    {

        DB::beginTransaction();
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image_path' => 'required|image|mimes:jpeg,png,jpg,gif,JPG|max:100000',
                'author' => 'required|string',
            ]);

            // Handle file upload
            $imagePath = $request->file('image_path')->store('images', 'public');


            // Create a new gallery record
            Galery::create([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'image_path' => $imagePath,
                'author' => $validatedData['author'],
            ]);

            Toastr::success('Has been Add successfully :)', 'Success');
            DB::commit();
            return redirect('admin/galery');
        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error('fail, Add Galery  :)', 'Error');
            return redirect()->back();
        }
    }

    public function adminGalery()
    {
        $galleries = Galery::paginate(3);
        return view('admin.galery', ['galeries' => $galleries]);
    }

    public function adminGaleryCreate()
    {
        return view('admin.galery-create');
    }

    // kegiatan

    public function kelolaKegiatan()
    {
        $blogs = Blog::orderBy('created_at', 'desc')->paginate(10);

        $kegiatann = Crudkegiatan::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.k-kegiatan', ['kegiatann' => $kegiatann]);
    }

    public function KegiatanCreate()
    {
        return view('admin.kegiatan-create');
    }

    public function storeKegiatan(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required',
            'deskripsi' => 'required',
            'tanggal_kegiatan' => 'required|date',
            'lokasi' => 'required',
            'author' => 'required',
            'gambar_kegiatan' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:3072',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar_kegiatan')) {
            $imagePath = $request->file('gambar_kegiatan')->store('images', 'public');
            $data['gambar_kegiatan'] = $imagePath;
        }

        Crudkegiatan::create($data);

        return redirect()->route('admin.k-kegiatan')
            ->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function editKegiatan($id)
    {
        $kegiatan = Crudkegiatan::where('id', $id)->first();
        return view('admin.kegiatan-edit', compact('kegiatan'));
    }



    public function updateKegiatan(Request $request, $id)
    {
        $request->validate([
            'nama_kegiatan' => 'required',
            'deskripsi' => 'required',
            'tanggal_kegiatan' => 'required|date',
            'lokasi' => 'required',
            'author' => 'required',
            'gambar_kegiatan' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:3072',
        ]);

        $kegiatan = Crudkegiatan::findOrFail($id);

        $data = $request->all();

        if ($request->hasFile('gambar_kegiatan')) {
            if ($kegiatan->gambar_kegiatan) {
                Storage::disk('public')->delete($kegiatan->gambar_kegiatan);
            }
            $imagePath = $request->file('gambar_kegiatan')->store('images', 'public');
            $data['gambar_kegiatan'] = $imagePath;
        }

        $kegiatan->update($data);

        return redirect()->route('admin.k-kegiatan')
            ->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroyKegiatan($id)
    {
        $kegiatan = Crudkegiatan::findOrFail($id);

        if ($kegiatan->gambar_kegiatan) {
            Storage::disk('public')->delete($kegiatan->gambar_kegiatan);
        }

        $kegiatan->delete();

        return redirect()->route('admin.k-kegiatan')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }



    public function kelolaBlog()
    {
        $blogs = Blog::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.k-blog', ['blogs' => $blogs]);
    }

    public function create()
    {
        return view('admin.blog-create');
    }

  public function store(Request $request)
{
    $request->validate([
        'nama_blog' => 'required|string|max:255',
        'tanggal_blog' => 'required|date',
        'deskripsi1' => 'required',
        'deskripsi2' => 'required',
        'author' => 'required|string|max:100',
        'gambar' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:3072',
    ]);

    $data = $request->all();

    if ($request->hasFile('gambar')) {
        $imageName = time() . '.' . $request->file('gambar')->extension();
        // Simpan gambar di folder 'public/blog'
        $request->file('gambar')->storeAs('public/blog', $imageName);
        $data['gambar'] = 'blog/' . $imageName; // Path relatif
    }

    Blog::create($data);

    return redirect()->route('admin.k-blog')->with('success', 'Blog berhasil ditambahkan.');
}


    public function edit($id)
    {
        $blog = Blog::where('id', $id)->first();

        return view('admin.blog-edit', compact('blog'));
    }

  public function update(Request $request, $id)
{
    $blog = Blog::findOrFail($id);

    $request->validate([
        'nama_blog' => 'required|string|max:255',
        'tanggal_blog' => 'required|date',
        'deskripsi1' => 'required',
        'deskripsi2' => 'required',
        'author' => 'required|string|max:100',
        'gambar' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:3072',
    ]);

    $data = $request->all();

    if ($request->hasFile('gambar')) {
        // Hapus gambar lama jika ada
        if ($blog->gambar) {
            Storage::delete('public/' . $blog->gambar);
        }

        // Simpan gambar baru di folder 'public/blog'
        $imageName = time() . '.' . $request->file('gambar')->extension();
      $request->file('gambar')->storeAs('public/blog', $imageName);
        $data['gambar'] = 'blog/' . $imageName; // Path relatif
    }

    $blog->update($data);

    return redirect()->route('admin.k-blog')->with('success', 'Blog berhasil diperbarui!');
}


   public function destroy($id)
    {
        // Cari blog berdasarkan ID
        $blog = Blog::findOrFail($id); // Akan memberikan error 404 jika ID tidak ditemukan

        // Hapus gambar jika ada
        if ($blog->gambar) {
            Storage::disk('public')->delete($blog->gambar);
        }

        // Hapus data blog
        $blog->delete();

        // Redirect ke halaman daftar blog dengan pesan sukses
        return redirect()->route('admin.k-blog')->with('success', 'Blog berhasil dihapus.');
    }

    //page import excel ke database
    public function pageImport()
    {
        return view('admin.import-users');
    }

    public function import(Request $request)
    {
        Excel::import(new UserImport, request()->file('file'));

        Toastr::success('Has been Add successfully :)', 'Success');
        return back();
    }
    public function pageImportAnggota()
{
    // Logika untuk menampilkan halaman impor anggota
    return view('admin.import-anggota');
}

public function import_anggota(Request $request)
{
    Excel::import(new AnggotaImport, request()->file('file'));

    Toastr::success('Anggota berhasil diimpor :)', 'Success');
    return back();
}

// export




    // korkom

    public function kelolaKorkom()
    {
        $korkom = ModelsKorkom::all();
        return view('admin.k-korkom', ['korkoms' => $korkom]);
    }

    public function createKorkom()
    {
        return view('admin.korkom-create');
    }

    public function editKorkom($id)
    {
        // Ambil data Korkom berdasarkan ID
        $korkom = ModelsKorkom::findOrFail($id);

        // Kirim data Korkom ke view
        return view('admin.korkom-edit', compact('korkom'));
    }




    public function updateKorkom(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3072',

        ]);

        $korkom = ModelsKorkom::findOrFail($id);
        $korkom->nama = $request->nama;
        $korkom->jabatan = $request->jabatan;

        if ($request->hasFile('gambar')) {
            if ($korkom->gambar) {
                Storage::delete('public/' . $korkom->gambar);
            }

            $imageName = time() . '.' . $request->gambar->extension();
            $request->gambar->storeAs('public/korkom', $imageName);
            $korkom->gambar = 'korkom/' . $imageName;
        }

        $korkom->save();

        return redirect()->route('admin.k-korkom', $id)->with('success', 'Korkom berhasil diupdate!');
    }

    // Method untuk menghapus Korkom
    public function deleteKorkom($id)
    {
        $korkom = ModelsKorkom::findOrFail($id);

        // Hapus gambar jika ada
        if ($korkom->gambar) {
            Storage::delete('public/' . $korkom->gambar);
        }

        $korkom->delete();

        return redirect()->route('admin.k-korkom')->with('success', 'Korkom berhasil dihapus!');
    }

    public function storeKorkom(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3072',

        ]);

        $korkoms = new ModelsKorkom();
        $korkoms->nama = $request->nama;
        $korkoms->jabatan = $request->jabatan;

        if ($request->hasFile('gambar')) {
            $imageName = time() . '.' . $request->gambar->extension();
            $request->gambar->storeAs('public/korkom', $imageName);
            $korkoms->gambar = 'korkom/' . $imageName;
        }

        $korkoms->save();

        return redirect()->route('admin.k-korkom')->with('success', 'Korkom berhasil ditambahkan!');
    }

    //komisariat
    public function kelolaKomisariat()
    {
        $komisariat = Komisariat::all();
        return view('admin.k-komis', ['komisariats' => $komisariat]);
    }

    public function createKomisariat()
    {
        return view('admin.komis-create');
    }

    public function editKomisariat($id_komisariat)
    {
        // Ambil data Komisariat berdasarkan ID_KOMISARIAT
        $komisariat = Komisariat::findOrFail($id_komisariat);

        // Kirim data Komisariat ke view
        return view('admin.komis-edit', compact('komisariat'));
    }


    public function updateKomisariat(Request $request, $id_komisariat)
    {
        // Validasi data request
        $validatedData = $request->validate([
            'komisariat' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3072',
        ]);

        // Temukan komisariat berdasarkan id
        $komisariat = Komisariat::findOrFail($id_komisariat);

        // Update nama komisariat
        $komisariat->komisariat = $validatedData['komisariat'];

        // Proses file gambar jika ada
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($komisariat->image) {
                Storage::delete('public/' . $komisariat->image);
            }

            // Simpan gambar baru
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/komisariat', $imageName);
            $komisariat->image = 'komisariat/' . $imageName;
        }

        // Simpan perubahan ke database
        $komisariat->save();

        // Redirect ke halaman daftar komisariat dengan pesan sukses
        return redirect()->route('admin.k-komis')->with('success', 'Komisariat berhasil diupdate!');
    }

    // Method untuk menghapus Komisariat
    public function deleteKomisariat($id_komisariat)
    {
        $komisariat = Komisariat::findOrFail($id_komisariat);

        // Hapus image jika ada
        if ($komisariat->image) {
            Storage::delete('public/' . $komisariat->image);
        }

        $komisariat->delete();

        return redirect()->route('admin.k-komis')->with('success', 'Komisariat berhasil dihapus!');
    }

    public function storeKomisariat(Request $request)
    {
        $request->valid_komisariatate([
            'id_komisariat',
            'komisariat' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:3072',

        ]);

        $komisariats = new Komisariat();
        $komisariats->komisariat = $request->komisariat;

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/komisariat', $imageName);
            $komisariats->image = 'komisariat/' . $imageName;
        }

        $komisariats->save();

        return redirect()->route('admin.k-komis')->with('success', 'Komisariat berhasil ditambahkan!');
    }
    
    
    public function indexComments(Request $request)
{
    $comments = PostComment::with('blog')->latest()->get(); // Ambil semua komentar

    $selectedComment = null;
    if ($request->has('id')) {
        $selectedComment = PostComment::with('blog')->find($request->id);
    }

    return view('admin.comment', compact('comments', 'selectedComment'));
}

    public function showComment($id)
    {
        $comment = PostComment::with('blog')->findOrFail($id); // Ambil komentar berdasarkan ID
        return view('admin.comment', compact('comment'));
    }

// In your CommentController.php
 public function approveComment($id)
    {
        $comment = PostComment::findOrFail($id);
        $comment->status = 'setuju'; // Ubah status menjadi 'approved'
        $comment->save();

        return redirect()->route('admin.comment')->with('success', 'Komentar berhasil disetujui.');
    }

    /**
     * Menghapus komentar berdasarkan ID.
     */
    public function deleteComment($id)
    {
        $comment = PostComment::findOrFail($id);
        $comment->delete();

        return redirect()->route('admin.comment')->with('success', 'Komentar berhasil dihapus.');
    }
    
    public function reject($id)
{
    $comment = PostComment::findOrFail($id);
    $comment->status = 'tolak';
    $comment->save();

    return redirect()->back()->with('success', 'Komentar berhasil ditolak.');
}


}
