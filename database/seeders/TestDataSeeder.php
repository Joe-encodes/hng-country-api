<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create some test countries with known data
        Country::create([
            'name' => 'Nigeria',
            'name_normalized' => 'nigeria',
            'capital' => 'Abuja',
            'region' => 'Africa',
            'population' => 206139589,
            'currency_code' => 'NGN',
            'exchange_rate' => 1600.23,
            'estimated_gdp' => 25767448125.2,
            'flag_url' => 'https://flagcdn.com/ng.svg',
            'last_refreshed_at' => now(),
        ]);

        Country::create([
            'name' => 'United States',
            'name_normalized' => 'united-states',
            'capital' => 'Washington, D.C.',
            'region' => 'Americas',
            'population' => 331002651,
            'currency_code' => 'USD',
            'exchange_rate' => 1.0,
            'estimated_gdp' => 331002651000.0,
            'flag_url' => 'https://flagcdn.com/us.svg',
            'last_refreshed_at' => now(),
        ]);

        // Add more test cases
        Country::create([
            'name' => 'No Currency Land',
            'name_normalized' => 'no-currency-land',
            'capital' => null,
            'region' => 'Test',
            'population' => 100,
            'currency_code' => null,
            'exchange_rate' => null,
            'estimated_gdp' => 0,
            'flag_url' => null,
            'last_refreshed_at' => now(),
        ]);
    }
}