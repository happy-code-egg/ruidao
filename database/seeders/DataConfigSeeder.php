<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DataConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            TechServiceTypesSeeder::class,
            ManuscriptScoringItemsSeeder::class,
            ProtectionCentersSeeder::class,
            PriceIndicesSeeder::class,
            InnovationIndicesSeeder::class,
        ]);
    }
}