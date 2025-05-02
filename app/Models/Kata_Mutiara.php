<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kata_Mutiara extends Model
{
    use HasFactory;

    protected $table = "kata_mutiara";
    protected $fillable = ['judul', 'pengarang'];
}
