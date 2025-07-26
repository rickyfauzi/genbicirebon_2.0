<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Crudkegiatan extends Model
{
    protected $table = 'crudkegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tanggal_kegiatan',
        'lokasi',
        'author',
        'gambar_kegiatan',
        'slug' // Menambahkan kolom gambar
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kegiatan) {
            $slug = Str::slug($kegiatan->nama_kegiatan);
            $count = Crudkegiatan::where('slug', 'LIKE', "{$slug}%")->count();
            $kegiatan->slug = $count ? "{$slug}-{$count}" : $slug;
        });

        static::updating(function ($kegiatan) {
            $slug = Str::slug($kegiatan->nama_kegiatan);
            $count = Crudkegiatan::where('slug', 'LIKE', "{$slug}%")
                ->where('id', '!=', $kegiatan->id)
                ->count();
            $kegiatan->slug = $count ? "{$slug}-{$count}" : $slug;
        });
    }
}
