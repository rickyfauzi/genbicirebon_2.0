<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',     
        'id_komisariat',
        'email',
        'join_date',
        'status',
        'role_name',
        'avatar',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // protected static function boot()
    // {
    //     parent::boot();
    //     self::creating(function ($model) {
    //         $getUser = self::orderBy('user_id', 'desc')->first();

    //         if ($getUser) {
    //             $latestID = intval(substr($getUser->user_id, 3));
    //             $nextID = $latestID + 1;
    //         } else {
    //             $nextID = 1;
    //         }
    //         $model->user_id = '000' . sprintf("%03s", $nextID);
    //         while (self::where('user_id', $model->user_id)->exists()) {
    //             $nextID++;
    //             $model->user_id = '000' . sprintf("%03s", $nextID);
    //         }
    //     });
    // }
}
