<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crudkegiatan extends Model
{
    protected $table = 'crudkegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tanggal_kegiatan',
        'lokasi',
        'author',
        'gambar_kegiatan' // Menambahkan kolom gambar
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
    ];
}
