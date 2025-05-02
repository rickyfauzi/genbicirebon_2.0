<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $table = 'blog';

    protected $fillable = [
        'nama_blog',
        'tanggal_blog',
        'deskripsi1',
        'deskripsi2',
        'author',
        'gambar',
    ];
    
    
    // Di model Blog
public function comments()
{
    return $this->hasMany(Comment::class);
}

}
