<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use HasApiTokens, HasFactory, HasRolesAndPermissions, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'image'
    ];

    protected $appends=[
        'image_path'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFirstNameAttribute($value){
        return ucfirst($value);
    }

    public function getLastNameAttribute($value){
        return ucfirst($value);
    }

    //returns the full URL of an image stored in the public directory
    public function getImagePathAttribute(){
        //useful for including images in views
        return asset('uploads/user_images/' . $this->image);
    }

}
