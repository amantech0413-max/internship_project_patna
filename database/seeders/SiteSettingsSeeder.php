<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SiteSettings::DEFAULTS as $key => $value) {
            Setting::query()->firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
