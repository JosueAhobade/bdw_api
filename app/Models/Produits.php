<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produits extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'categorie',
        'prix',
        'devise',
        'image',
        'status',
    ];
}
