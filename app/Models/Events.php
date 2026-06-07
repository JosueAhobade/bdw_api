<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'status',
        'is_online',
        'online_link',
        'capacity',
        'image',
    ];


    public function formation()
    {
        return $this->belongsTo(Formations::class);
    }
    
}
