<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inscriptions;
use App\Models\Formateurs;
use App\Models\Formations;

class Sessions extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id',
        'formateur_id',
        'date_debut',
        'date_fin',
        'lieu',
        'nombre_places',
        'places_restantes',
        'type',
        'lien_visio',
        'status',
    ];

    public function formation()
    {
        return $this->belongsTo(Formations::class);
    }

    public function formateur()
    {
        return $this->belongsTo(Formateurs::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscriptions::class);
    }
}
