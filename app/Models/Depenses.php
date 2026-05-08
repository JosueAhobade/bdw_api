<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employes;

class Depenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'montant',
        'devise',
        'mode_paiement',
        'reference_paiement',
        'type_depense',
        'description',
        'date_depense',
        'employe_id',
        'fournisseur_id',
        'preuve',
        'status',
    ];

    public function employe()
    {
        return $this->belongsTo(Employes::class);
    }
}
