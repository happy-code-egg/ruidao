<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConfigDataSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ProcessTypesSeeder::class,
            CopyrightExpediteTypesSeeder::class,
            OurCompaniesSeeder::class,
            CommissionTypesSeeder::class,
            CommissionSettingsSeeder::class,
        ]);
    }
}