<?php

namespace Database\Seeders;

use App\Models\ChaletSettings;
use Illuminate\Database\Seeder;

class ChaletSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = ChaletSettings::current();

        if ($settings->wasRecentlyCreated) {
            $this->command->info('Chalet settings row created with default values (شاليهات السراة).');
            $this->command->warn('Update prices, contact info, and feature list from the admin Settings page.');
        } else {
            $this->command->info("Chalet settings already exist (id={$settings->id}); skipped.");
        }
    }
}