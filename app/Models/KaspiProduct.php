<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KaspiProduct extends Model
{
    /** @use HasFactory<\Database\Factories\KaspiProductFactory> */
    use HasFactory;

    protected $fillable = [
        'product_url',
        'author_price',
        'last_checked_at',
    ];
}
