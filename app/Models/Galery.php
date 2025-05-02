<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galery extends Model
{
    use HasFactory;
    protected $table = 'galeries';
    protected $primaryKey = 'id';
    protected $fillable = [
        'title',
        'image_path',
        'description',
        'author'
    ];
}
