<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_anggota',
        'nama',
        'prodi',
        'jk',
        'status',
        'id_komisariat',
        'id_user',
        'updated_at',
        'created_at'
    ];    
}
