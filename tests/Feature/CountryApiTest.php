<?php

namespace Tests\Feature;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CountryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_endpoint_fetches_and_stores_countries()
    {
        // Mock external APIs
        Http::fake([
            'restcountries.com/*' => Http::response([
                [
                    'name' => 'Nigeria',
                    'capital' => 'Abuja',
                    'region' => 'Africa',
                    'population' => 206139589,
                    'flag' => 'https://flagcdn.com/ng.svg',
                    'currencies' => [
                        ['code' => 'NGN', 'name' => 'Nigerian Naira']
                    ]
                ],
                [
                    'name' => 'Ghana',
                    'capital' => 'Accra',
                    'region' => 'Africa',
                    'population' => 31072940,
                    'flag' => 'https://flagcdn.com/gh.svg',
                    'currencies' => [
                        ['code' => 'GHS', 'name' => 'Ghanaian Cedi']
                    ]
                ]
            ]),
            'open.er-api.com/*' => Http::response([
                'rates' => [
                    'NGN' => 1600.23,
                    'GHS' => 15.34,
                ]
            ], 200)
        ]);

        $response = $this->postJson('/api/countries/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_countries',
                'last_refreshed_at'
            ]);

        $this->assertDatabaseHas('countries', [
            'name' => 'Nigeria',
            'region' => 'Africa',
        ]);

        $this->assertDatabaseHas('countries', [
            'name' => 'Ghana',
            'region' => 'Africa',
        ]);
    }

    public function test_get_all_countries()
    {
        Country::factory()->create([
            'name' => 'Nigeria',
            'region' => 'Africa',
            'currency_code' => 'NGN',
        ]);

        Country::factory()->create([
            'name' => 'Ghana',
            'region' => 'Africa',
            'currency_code' => 'GHS',
        ]);

        $response = $this->getJson('/api/countries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'count',
                'filters_applied'
            ]);

        $this->assertEquals(2, $response->json('count'));
    }

    public function test_get_countries_with_region_filter()
    {
        Country::factory()->create([
            'name' => 'Nigeria',
            'region' => 'Africa',
        ]);

        Country::factory()->create([
            'name' => 'USA',
            'region' => 'Americas',
        ]);

        $response = $this->getJson('/api/countries?region=Africa');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('count'));
        $this->assertEquals('Nigeria', $response->json('data.0.name'));
    }

    public function test_get_countries_with_currency_filter()
    {
        Country::factory()->create([
            'name' => 'Nigeria',
            'currency_code' => 'NGN',
        ]);

        Country::factory()->create([
            'name' => 'USA',
            'currency_code' => 'USD',
        ]);

        $response = $this->getJson('/api/countries?currency=NGN');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('count'));
        $this->assertEquals('NGN', $response->json('data.0.currency_code'));
    }

    public function test_get_countries_with_sorting()
    {
        Country::factory()->create([
            'name' => 'Nigeria',
            'estimated_gdp' => 1000,
        ]);

        Country::factory()->create([
            'name' => 'Ghana',
            'estimated_gdp' => 2000,
        ]);

        $response = $this->getJson('/api/countries?sort=gdp_desc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Ghana', $data[0]['name']);
    }

    public function test_get_country_by_name()
    {
        Country::factory()->create([
            'name' => 'Nigeria',
            'region' => 'Africa',
        ]);

        $response = $this->getJson('/api/countries/Nigeria');

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Nigeria',
                'region' => 'Africa',
            ]);
    }

    public function test_get_country_by_name_returns_404_when_not_found()
    {
        $response = $this->getJson('/api/countries/Unknown');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Country not found',
            ]);
    }

    public function test_delete_country()
    {
        $country = Country::factory()->create([
            'name' => 'Nigeria',
        ]);

        $response = $this->deleteJson('/api/countries/Nigeria');

        $response->assertStatus(204);
        $this->assertDatabaseMissing('countries', [
            'name' => 'Nigeria',
        ]);
    }

    public function test_delete_country_returns_404_when_not_found()
    {
        $response = $this->deleteJson('/api/countries/Unknown');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Country not found',
            ]);
    }

    public function test_status_endpoint()
    {
        Country::factory()->count(3)->create();

        $response = $this->getJson('/api/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_countries',
                'last_refreshed_at'
            ]);

        $this->assertEquals(3, $response->json('total_countries'));
    }
}

