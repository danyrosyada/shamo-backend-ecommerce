<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProductGallery extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'products_id',
        'url',
    ];

    // mutation buat convert url biar bisa di terima laravel nanti outputnya full url buat mangil gambar https://blabla.com/foto.png
    public function getUrlAttribute($url)
    {
        return config('app.url') . Storage::url($url);
    }
}
