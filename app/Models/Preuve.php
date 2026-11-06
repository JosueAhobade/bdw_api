<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Preuve extends Model
{
    public const DISK = 'local';

    protected $fillable = [
        'original_name',
        'path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected $hidden = [
        'path',
        'preuvable_type',
    ];

    protected static function booted()
    {
        static::deleted(function (Preuve $preuve) {
            Storage::disk(self::DISK)->delete($preuve->path);
        });
    }

    public function preuvable()
    {
        return $this->morphTo();
    }
}
