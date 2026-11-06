<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Mouvement sur un compte courant d'associé.
 */
class AssocieMouvement extends Model
{
    public const CREDITS = ['apport', 'interets'];
    public const DEBITS = ['remboursement', 'retrait'];
    public const TYPES = [...self::CREDITS, ...self::DEBITS];

    protected $fillable = [
        'associe_id',
        'type',
        'montant',
        'date_mouvement',
        'mode_paiement',
        'reference_paiement',
        'description',
        'created_by',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_mouvement' => 'date:Y-m-d',
    ];

    protected $appends = ['sens'];

    protected static function booted()
    {
        // Supprime aussi les fichiers de preuve liés
        static::deleting(function (AssocieMouvement $mouvement) {
            $mouvement->preuves->each->delete();
        });
    }

    /**
     * SQL : montant signé (+ crédit, - débit).
     */
    public static function signedAmountSql(): string
    {
        $credits = "'" . implode("','", self::CREDITS) . "'";

        return "CASE WHEN type IN ($credits) THEN montant ELSE -montant END";
    }

    public function getSensAttribute(): string
    {
        return in_array($this->type, self::CREDITS, true) ? 'credit' : 'debit';
    }

    public function associe()
    {
        return $this->belongsTo(Associes::class, 'associe_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function preuves()
    {
        return $this->morphMany(Preuve::class, 'preuvable')->latest();
    }
}
