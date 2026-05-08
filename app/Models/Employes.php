<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Depenses;
use App\Models\Recettes;
use App\Models\Objectifs;

class Employes extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'matricule',
        'email',
        'telephone',
        'adresse',
        'dernier_diplome',
        'date_naissance',
        'date_embauche',
        'poste',
        'departement',
        'salaire',
        'status',
    ];

    public function depenses()
    {
        return $this->hasMany(Depenses::class);
    }

    public function recettes()
    {
        return $this->hasMany(Recettes::class);
    }

    public function objectifs()
    {
        return $this->hasMany(Objectifs::class);
    }
}
