<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inscriptions;

class Apprenants extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'date_naissance',
        'genre',
        'niveau_etude',
        'profession',
        'employeur',
        'num_identite',
        'photo',
        'status',
    ];

    public function inscriptions()
    {
        return $this->hasMany(Inscriptions::class);
    }
}
