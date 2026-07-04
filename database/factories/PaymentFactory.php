<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'other']),
            'payment_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'receipt_url' => null,
            'note' => null,
        ];
    }

    public function cash(): self
    {
        return $this->state(fn () => ['payment_method' => 'cash']);
    }

    public function bankTransfer(): self
    {
        return $this->state(fn () => ['payment_method' => 'bank_transfer']);
    }
}