<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    use HasTranslations;

    use HasFactory;
    protected $fillable = [
        'Name',
        'image_path'
    ];

    public $translatable = ['Name'];




    public function Products()
    {
        return $this->hasMany(Product::class);
    }
}
