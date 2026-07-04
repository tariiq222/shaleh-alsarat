<?php

namespace App\Models;

use Database\Factories\ChaletSettingsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $features
 * @property string|null $location_text
 * @property string|null $map_url
 * @property string|null $whatsapp_number
 * @property float $weekday_price
 * @property float $weekend_price
 * @property string $check_in_time
 * @property string $check_out_time
 * @property bool $is_active
 */
class ChaletSettings extends Model
{
    /** @use HasFactory<ChaletSettingsFactory> */
    use HasFactory;

    protected $table = 'chalet_settings';

    protected $fillable = [
        'name',
        'description',
        'features',
        'location_text',
        'map_url',
        'whatsapp_number',
        'weekday_price',
        'weekend_price',
        'check_in_time',
        'check_out_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weekday_price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Singleton helper: returns the only settings row, creating it if missing.
     * The system is designed for exactly ONE chalet, so we always operate on id=1.
     */
    public static function current(): self
    {
        $settings = static::first();

        if (! $settings) {
            $settings = static::create([
                'name' => 'شاليهات السراة',
                'description' => null,
                'features' => null,
                'location_text' => null,
                'map_url' => null,
                'whatsapp_number' => null,
                'weekday_price' => 0,
                'weekend_price' => 0,
                'check_in_time' => '16:00',
                'check_out_time' => '12:00',
                'is_active' => true,
            ]);
        }

        return $settings;
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ChaletPhoto::class)->orderBy('sort_order');
    }

    /**
     * @return Collection<int, string>
     */
    public function featuresList(): Collection
    {
        if (! $this->features) {
            return collect();
        }

        return collect(preg_split('/\R/u', $this->features))
            ->map(fn ($f) => trim($f))
            ->filter()
            ->values();
    }

    public function whatsappLink(?string $message = null): ?string
    {
        if (! $this->whatsapp_number) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', $this->whatsapp_number);

        $url = "https://wa.me/{$phone}";
        if ($message) {
            $url .= '?text='.rawurlencode($message);
        }

        return $url;
    }
}