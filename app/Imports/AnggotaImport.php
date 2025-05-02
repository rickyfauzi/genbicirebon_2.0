<?php

namespace App\Imports;

use App\Models\Anggota;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AnggotaImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Anggota([
            'nama' =>$row['nama'],
            'prodi' => $row['prodi'],
            'jk' => $row['jk'],
            'status' => $row['status'],
            'id_komisariat' => $row['id_komisariat'], 
            'id_user' => $row['id_user'], 
        ]);
    }
}
