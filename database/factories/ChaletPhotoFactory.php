<?php

namespace Database\Factories;

use App\Models\ChaletPhoto;
use App\Models\ChaletSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChaletPhoto>
 */
class ChaletPhotoFactory extends Factory
{
    protected $model = ChaletPhoto::class;

    public function definition(): array
    {
        return [
            'chalet_settings_id' => ChaletSettings::factory(),
            'path' => 'photos/sample-'.fake()->uuid().'.jpg',
            'caption' => null,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}