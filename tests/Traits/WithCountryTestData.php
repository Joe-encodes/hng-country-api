<?php

namespace Tests\Traits;

use App\Models\Country;
use Illuminate\Support\Facades\Http;

trait WithCountryTestData
{
    protected function mockSuccessfulExternalApis(): void
    {
        Http::fake([
            'restcountries.com/*' => Http::response([
                [
                    'name' => 'Nigeria',
                    'capital' => 'Abuja',
                    'region' => 'Africa',
                    'population' => 206139589,
                    'flag' => 'https://flagcdn.com/ng.svg',
                    'currencies' => [['code' => 'NGN', 'name' => 'Nigerian Naira']]
                ],
                [
                    'name' => 'Ghana',
                    'capital' => 'Accra',
                    'region' => 'Africa',
                    'population' => 31072940,
                    'flag' => 'https://flagcdn.com/gh.svg',
                    'currencies' => [['code' => 'GHS', 'name' => 'Ghanaian Cedi']]
                ],
                [
                    'name' => 'United States',
                    'capital' => 'Washington, D.C.',
                    'region' => 'Americas',
                    'population' => 331002651,
                    'flag' => 'https://flagcdn.com/us.svg',
                    'currencies' => [['code' => 'USD', 'name' => 'US Dollar']]
                ]
            ]),
            'open.er-api.com/*' => Http::response([
                'rates' => [
                    'NGN' => 1600.23,
                    'GHS' => 15.34,
                    'USD' => 1.0
                ]
            ])
        ]);
    }

    protected function createTestCountries(): void
    {
        Country::factory()->createMany([
            [
                'name' => 'Nigeria',
                'name_normalized' => 'nigeria',
                'region' => 'Africa',
                'currency_code' => 'NGN',
                'estimated_gdp' => 1000000
            ],
            [
                'name' => 'Ghana',
                'name_normalized' => 'ghana',
                'region' => 'Africa',
                'currency_code' => 'GHS',
                'estimated_gdp' => 500000
            ],
            [
                'name' => 'United States',
                'name_normalized' => 'united-states',
                'region' => 'Americas',
                'currency_code' => 'USD',
                'estimated_gdp' => 2000000
            ]
        ]);
    }

    protected function assertCountryResponseStructure($response): void
    {
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'capital',
                'region',
                'population',
                'currency_code',
                'exchange_rate',
                'estimated_gdp',
                'flag_url',
                'last_refreshed_at'
            ]
        ]);
    }
}