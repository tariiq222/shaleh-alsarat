<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-30 days', '+60 days')->format('Y-m-d');
        $endDate = date('Y-m-d', strtotime($startDate . ' +'.fake()->numberBetween(1, 5).' days'));

        return [
            'booking_number' => 'CHL-'.date('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'customer_name' => fake('ar_SA')->name(),
            'customer_phone' => '+9665'.fake()->numerify('########'),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_amount' => fake()->randomFloat(2, 500, 5000),
            'deposit_amount' => fake()->randomFloat(2, 100, 1000),
            'remaining_amount' => 0,
            'booking_status' => 'pending',
            'payment_status' => 'unpaid',
            'source' => 'admin',
            'notes' => null,
        ];
    }

    public function confirmed(): self
    {
        return $this->state(fn () => ['booking_status' => 'confirmed']);
    }

    public function cancelled(): self
    {
        return $this->state(fn () => ['booking_status' => 'cancelled']);
    }

    public function completed(): self
    {
        return $this->state(fn () => ['booking_status' => 'completed']);
    }

    public function paid(): self
    {
        return $this->state(fn () => array_merge($this->definition(), ['payment_status' => 'paid']));
    }

    public function fromWebsite(): self
    {
        return $this->state(fn () => [
            'source' => 'website',
            'inquiry_id' => Inquiry::factory(),
        ]);
    }
}