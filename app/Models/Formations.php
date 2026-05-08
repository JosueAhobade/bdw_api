<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Formateurs;
use App\Models\Sessions;

class Formations extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description0',
        'objectif',
        'programme',
        'duree',
        'niveau',
        'type',
        'prix',
        'langue',
        'formateur_id',
        'date_debut',
        'date_fin',
        'nombre_places',
        'status',
    ];

    public function formateur()
    {
        return $this->belongsTo(Formateurs::class);
    }

    public function sessions()
    {
        return $this->hasMany(Sessions::class);
    }
}
