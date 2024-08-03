<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, Translatable;
    protected $guarded=[];
    public $translatedAttributes =['name'];

    public function products(){

        return $this->hasMany(Product::class);
    }
}
