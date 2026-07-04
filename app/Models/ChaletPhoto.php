<?php

namespace App\Models;

use Database\Factories\ChaletPhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChaletPhoto extends Model
{
    /** @use HasFactory<ChaletPhotoFactory> */
    use HasFactory;

    protected $table = 'chalet_photos';

    protected $fillable = [
        'chalet_settings_id',
        'path',
        'caption',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function chalet(): BelongsTo
    {
        return $this->belongsTo(ChaletSettings::class, 'chalet_settings_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Delete the underlying file from storage when the model is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (self $photo) {
            if ($photo->path && Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }
        });
    }
}