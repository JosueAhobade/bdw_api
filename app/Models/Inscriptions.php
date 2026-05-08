<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Apprenant;
use App\Models\Session;
use App\Models\Recettes;


class Inscriptions extends Model
{
    use HasFactory;

    protected $fillable = [
        'apprenant_id',
        'session_id',
        'date_inscription',
        'status',
        'presence',
        'note_finale',
        'certificat_delivre',
        'certificat_numero',
        'commentaires',
    ];

    public function apprenant()
    {
        return $this->belongsTo(Apprenants::class);
    }

    public function session()
    {
        return $this->belongsTo(Sessions::class);
    }

    public function recettes()
    {
        return $this->hasMany(Recettes::class);
    }
}
