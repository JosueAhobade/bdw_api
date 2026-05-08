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

    public function employe()
    {
        return $this->belongsTo(Employes::class);
    }
}
