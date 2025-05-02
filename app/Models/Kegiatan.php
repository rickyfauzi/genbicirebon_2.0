<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_kegiatan',
        'nama_kegiatan',
        'tgl_pelaksanaan',
        'poin',
        'jenis',
        'id_komisariat',
        'created_at',
        'updated_at',
    ];    
}
