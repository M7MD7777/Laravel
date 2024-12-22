<?php

namespace App\Models;

use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    use HasTranslations;

    use HasFactory;
    protected $fillable = [
        'Name',
        'Price',
        'Description',
        'image_path',
        'category_id'
    ];

    public $translatable = ['Name', 'Description'];


    public function Category()
    {
        return $this->belongsTo(Category::class);
    }
}
