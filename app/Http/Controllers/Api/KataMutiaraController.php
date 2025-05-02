<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kata_Mutiara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KataMutiaraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Kata_Mutiara::orderBy('Judul','asc')->get();
        return response()->json([
            'status'=>true,
            'message' =>'Data Ditemukan',
            'data' =>$data
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $dataKata = new Kata_Mutiara;
        $rules = [
            'judul' =>'required',
            'pengarang'=> 'required'
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' =>'Gagal Memasukan Data',
                'data' => $validator->errors()
            ]);
        }
        $dataKata->judul = $request->judul;
        $dataKata->pengarang = $request->pengarang;

        $post = $dataKata->save();

        return response()->json([
            'status'=>true,
            'message' =>'Data Sukses Dimasukan'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Kata_Mutiara::find($id);
        if($data){
            return response()->json([
                'status'=>true,
                'message' =>'Data Ditemukan',
                'data' =>$data
            ],200);
        }else{
            return response()->json([
                'status'=>false,
                'message' =>'Data Tidak Ditemukan',
            ],404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $dataKata = Kata_Mutiara::find($id);
        if(empty($dataKata)){
            return response()->json([
                'status'=>false,
                'message' =>'Data Tidak Ditemukan',

            ]);
        }

        $rules = [
            'judul' =>'required',
            'pengarang'=> 'required'
        ];
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json([
                'status'=>false,
                'message' =>'Gagal Melakukan Update Data',
                'data' => $validator->errors()
            ]);
        }
        $dataKata->judul = $request->judul;
        $dataKata->pengarang = $request->pengarang;

        $post = $dataKata->save();

        return response()->json([
            'status'=>true,
            'message' =>'Data Terupdate'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dataKata = Kata_Mutiara::find($id);
        if(empty($dataKata)){
            return response()->json([
                'status'=>false,
                'message' =>'Data Tidak Ditemukan',

            ],404);
        }

        $post = $dataKata->delete();

        return response()->json([
            'status'=>true,
            'message' =>'Sukses Melakukan Delete Data'
        ]);
    }
}
