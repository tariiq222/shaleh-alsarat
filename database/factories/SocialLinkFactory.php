<?php

namespace Database\Factories;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialLink>
 */
class SocialLinkFactory extends Factory
{
    protected $model = SocialLink::class;

    public function definition(): array
    {
        $platform = fake()->randomElement(['whatsapp', 'instagram', 'twitter', 'snapchat', 'tiktok']);

        return [
            'name' => match ($platform) {
                'whatsapp' => 'واتساب',
                'instagram' => 'انستقرام',
                'twitter' => 'إكس',
                'snapchat' => 'سناب شات',
                'tiktok' => 'تيك توك',
                default => 'آخر',
            },
            'platform' => $platform,
            'url' => 'https://'.fake()->domainName().'/'.fake()->userName(),
            'handle' => '@'.fake()->userName(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}