<?php

namespace Database\Factories;

use App\Models\BlockedDate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlockedDate>
 */
class BlockedDateFactory extends Factory
{
    protected $model = BlockedDate::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d');
        $endDate = date('Y-m-d', strtotime($startDate . ' +'.fake()->numberBetween(1, 5).' days'));

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => fake('ar_SA')->randomElement(['صيانة دورية', 'تجديدات', 'إغلاق موسمي']),
        ];
    }
}