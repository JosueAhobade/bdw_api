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
        'status',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_depense' => 'date:Y-m-d',
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
