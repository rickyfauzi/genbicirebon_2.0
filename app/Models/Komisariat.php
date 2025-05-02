<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komisariat extends Model
{
    use HasFactory;

    protected $table = 'komisariats'; // Nama tabel
    protected $primaryKey = 'id_komisariat'; // Kolom primary key
    public $incrementing = false; // Jika id_komisariat bukan auto-increment
    protected $keyType = 'string'; // Jika id_komisariat adalah tipe string
    public $timestamps = false; // Nonaktifkan timestamps otomatis

    protected $fillable = [
        'komisariat',
        'image',
    ];    
    
}
