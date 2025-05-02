<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
  public function model(array $row)
 {
        return new User([
            'name' => $row['name'],
            'id_komisariat' =>$row['id_komisariat'],
            'email' => $row['email'],
            'role_name' => $row['role_name'],
            'password' => Hash::make($row['password']),
            'avatar' => $row['avatar'],
        ]);
    }

}
