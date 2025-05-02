<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;

    // Tentukan nama tabel yang sesuai
    protected $table = 'post_comments';

    // Tentukan kolom-kolom yang dapat diisi (mass assignable)
    protected $fillable = [
        'nama',
        'alamat',
        'komentar',
        'blog_id',
        'status',
    ];

    // Relasi ke model Blog
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id', 'id');
    }
    
    // Di model Comment
    
     public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


}
