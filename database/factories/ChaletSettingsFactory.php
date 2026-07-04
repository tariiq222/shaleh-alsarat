<?php

namespace Database\Factories;

use App\Models\ChaletSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChaletSettings>
 */
class ChaletSettingsFactory extends Factory
{
    protected $model = ChaletSettings::class;

    public function definition(): array
    {
        return [
            'name' => 'شاليهات السراة',
            'description' => 'استرخِ في أحضان الطبيعة مع إطلالة خلابة على الجبال.',
            'features' => "إطلالة بانورامية\nمسبح خاص\nشواء خارجي\nواي فاي مجاني\nموقف سيارات",
            'location_text' => 'منطقة السراة، المملكة العربية السعودية',
            'map_url' => 'https://maps.google.com/?q=18.3,42.7',
            'whatsapp_number' => '+966500000000',
            'weekday_price' => 600,
            'weekend_price' => 900,
            'check_in_time' => '16:00',
            'check_out_time' => '12:00',
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}