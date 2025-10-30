<?php

namespace Tests\Feature;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CountryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_refresh_endpoint_fetches_and_stores_countries()
    {
        Storage::fake('public');

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
                'last_refreshed_at',
                'message'
            ]);

        $this->assertDatabaseHas('countries', [
            'name' => 'Nigeria',
            'region' => 'Africa',
        ]);

        $this->assertDatabaseHas('countries', [
            'name' => 'Ghana',
            'region' => 'Africa',
        ]);

        // Assert image was created
        $this->assertTrue(Storage::disk('public')->exists('cache/summary.png'));
    }

    public function test_refresh_handles_country_with_empty_currencies()
    {
        Http::fake([
            'restcountries.com/*' => Http::response([
                [
                    'name' => 'No Currency Land',
                    'population' => 100,
                    'currencies' => [] // Empty currencies array
                ]
            ]),
            'open.er-api.com/*' => Http::response(['rates' => ['USD' => 1]])
        ]);

        $this->postJson('/api/countries/refresh')->assertStatus(200);

        $this->assertDatabaseHas('countries', [
            'name' => 'No Currency Land',
            'currency_code' => null,
            'exchange_rate' => null,
            'estimated_gdp' => 0, // Should be 0 as per requirements
        ]);
    }

    public function test_country_image_endpoint_returns_image()
    {
        Storage::fake('public');
        
        // Create a dummy image first
        $image = imagecreate(100, 100);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        
        Storage::disk('public')->makeDirectory('cache');
        imagepng($image, Storage::disk('public')->path('cache/summary.png'));
        imagedestroy($image);

        $response = $this->get('/api/countries/image');
        
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_country_image_404_when_not_exists()
    {
        Storage::fake('public');
        
        $response = $this->get('/api/countries/image');
        
        $response->assertStatus(404)
            ->assertJson(['error' => 'Summary image not found']);
    }

    public function test_refresh_handles_currency_not_in_rates_api()
    {
        Http::fake([
            'restcountries.com/*' => Http::response([
                [
                    'name' => 'Unlisted Currency Country',
                    'population' => 500,
                    'currencies' => [['code' => 'XYZ']]
                ]
            ]),
            'open.er-api.com/*' => Http::response(['rates' => ['USD' => 1]]) // XYZ not in rates
        ]);

        $this->postJson('/api/countries/refresh')->assertStatus(200);

        $this->assertDatabaseHas('countries', [
            'name' => 'Unlisted Currency Country',
            'currency_code' => 'XYZ',
            'exchange_rate' => null,
            'estimated_gdp' => null,
        ]);
    }

    public function test_refresh_updates_existing_country_case_insensitively()
    {
        // 1. Create an initial country
        Country::factory()->create(['name' => 'Nigeria', 'name_normalized' => 'nigeria', 'population' => 100]);
        $this->assertDatabaseCount('countries', 1);

        // 2. Mock API to return "nigeria" (lowercase) with new data
        Http::fake([
            'restcountries.com/*' => Http::response([
                ['name' => 'nigeria', 'population' => 200, 'currencies' => [['code' => 'NGN']]]
            ]),
            'open.er-api.com/*' => Http::response(['rates' => ['NGN' => 1600]])
        ]);

        // 3. Run refresh
        $this->postJson('/api/countries/refresh')->assertStatus(200);

        // 4. Assert no new country was created and the existing one was updated
        $this->assertDatabaseCount('countries', 1);
        $this->assertDatabaseHas('countries', [
            'name_normalized' => 'nigeria',
            'population' => 200, // Population updated
        ]);
    }

    public function test_refresh_fails_if_countries_api_is_down()
    {
        Http::fake(['restcountries.com/*' => Http::response(null, 500)]);

        Country::factory()->create(['name' => 'Existing Country']);

        $response = $this->postJson('/api/countries/refresh');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'External data source unavailable. No data was changed.']);

        // Ensure database was not changed
        $this->assertDatabaseCount('countries', 1);
    }

    public function test_refresh_fails_if_rates_api_is_down()
    {
        Http::fake([
            'restcountries.com/*' => Http::response([['name' => 'Nigeria', 'population' => 100]]),
            'open.er-api.com/*' => Http::response(null, 500)
        ]);

        Country::factory()->create(['name' => 'Existing Country']);

        $response = $this->postJson('/api/countries/refresh');

        $response->assertStatus(503)
                 ->assertJson(['error' => 'External data source unavailable. No data was changed.']);

        // Ensure database was not changed
        $this->assertDatabaseCount('countries', 1);
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

        $response->assertStatus(200);
        $this->assertCount(2, $response->json());
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
        $this->assertCount(1, $response->json());
        $this->assertEquals('Nigeria', $response->json('0.name'));
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
        $this->assertCount(1, $response->json());
        $this->assertEquals('NGN', $response->json('0.currency_code'));
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
        $data = $response->json();
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

        $response->assertStatus(200);
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
