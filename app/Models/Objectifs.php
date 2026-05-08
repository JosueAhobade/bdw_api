<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employes;


class Objectifs extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'type',
        'periode',
        'objectif_valeur',
        'unite',
        'date_debut',
        'date_fin',
        'employe_id',
        'progression',
        'status',
    ];

    public function employe()
    {
        return $this->belongsTo(Employes::class);
    }
}
