<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Associé et son compte courant d'associé.
 * Solde = apports + intérêts - remboursements - retraits
 * (ce que la société doit à l'associé).
 */
class Associes extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'fonction',
        'part',
        'devise',
        'date_adhesion',
        'notes',
    ];

    protected $casts = [
        'part' => 'decimal:2',
        'date_adhesion' => 'date:Y-m-d',
    ];

    public function mouvements()
    {
        return $this->hasMany(AssocieMouvement::class, 'associe_id');
    }

    /**
     * Ajoute total_credits, total_debits, mouvements_count et solde à la requête.
     */
    public function scopeWithSoldes($query)
    {
        return $query
            ->withSum(['mouvements as total_credits' => fn ($q) => $q->whereIn('type', AssocieMouvement::CREDITS)], 'montant')
            ->withSum(['mouvements as total_debits' => fn ($q) => $q->whereIn('type', AssocieMouvement::DEBITS)], 'montant')
            ->withCount('mouvements')
            ->withMax('mouvements as dernier_mouvement', 'date_mouvement');
    }

    protected $appends = ['solde'];

    public function getSoldeAttribute(): ?float
    {
        if (! array_key_exists('total_credits', $this->attributes)) {
            return null;
        }

        return round((float) $this->attributes['total_credits'] - (float) ($this->attributes['total_debits'] ?? 0), 2);
    }
}
