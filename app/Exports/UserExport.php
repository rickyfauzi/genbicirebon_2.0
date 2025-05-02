<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return User::select('id','id_komisariat', 'name', 'email', 'role_name')->get();
    }

    public function headings(): array
    {
        return ["ID_USER", "ID_KOMISARIAT", "NAME", "EMAIL", "ROLE_NAME"];
    }
}
