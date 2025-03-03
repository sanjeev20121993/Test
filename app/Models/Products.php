<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'cat_id',
        'prod_name',
        'prod_description',
        'qty',
        'amount',
    ];
    
    public function images()
    {
        return $this->hasMany(Product_images::class, 'product_id');
    }
}
