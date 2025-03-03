<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_images extends Model
{
    use HasFactory;

    protected $table = 'products_images';

    protected $fillable = [
        'product_id',
        'image_path',
    ];
     
    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }

}
