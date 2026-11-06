<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inscriptions;
use App\Models\Employes;


class Recettes extends Model
{
    use HasFactory;

    protected $fillable = [
        'montant',
        'devise',
        'mode_paiement',
        'reference_paiement',
        'type_recette',
        'description',
        'date_recette',
        'inscription_id',
        'employe_id',
        'status',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscriptions::class);
    }

    protected $casts = [
        'montant' => 'decimal:2',
        'date_recette' => 'date:Y-m-d',
    ];

    protected static function booted()
    {
        // Supprime aussi les fichiers de preuve liés
        static::deleting(function ($operation) {
            $operation->preuves->each->delete();
        });
    }

    public function preuves()
    {
        return $this->morphMany(Preuve::class, 'preuvable')->latest();
    }

    public function employe()
    {
        return $this->belongsTo(Employes::class);
    }
}
