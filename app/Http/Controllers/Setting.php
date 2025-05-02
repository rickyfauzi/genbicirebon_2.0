<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komisariat;
use App\Models\User;

class Setting extends Controller
{
    // index page setting
    public function index()
    {
        $sidebar_komis      = Komisariat::all();
        return view('setting.settings', compact('sidebar_komis'));
    }

    public function update_user_akun(Request $request)
    {
    
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
    
        $validatedData['password'] = Hash::make($validatedData['password']);
    
        User::update($validatedData);
    
        Toastr::success('User Akun Update successfully :)','Success');
        return redirect('setting/setting-user');
    }
}
