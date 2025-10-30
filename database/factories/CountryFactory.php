<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name' => $name = $this->faker->unique()->country,
            'name_normalized' => \Illuminate\Support\Str::slug(mb_strtolower($name)),
            'capital' => $this->faker->city,
            'region' => $this->faker->randomElement(['Africa', 'Americas', 'Asia', 'Europe', 'Oceania']),
            'population' => $this->faker->numberBetween(1000000, 1000000000),
            'currency_code' => strtoupper($this->faker->unique()->currencyCode),
            'exchange_rate' => $this->faker->randomFloat(2, 0.1, 5000),
            'estimated_gdp' => $this->faker->randomFloat(2, 0, 1000000000),
            'flag_url' => $this->faker->imageUrl(640, 480, 'country', true),
            'last_refreshed_at' => now(),
        ];
    }
}




