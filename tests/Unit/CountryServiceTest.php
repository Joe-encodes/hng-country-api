<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CountryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CountryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CountryService::class);
    }

    public function test_normalize_country_name()
    {
        $countries = [
            'United States of America' => 'united-states-of-america',
            'São Tomé and Príncipe' => 'sao-tome-and-principe',
            'NIGERIA' => 'nigeria',
            'côte d\'ivoire' => 'cote-divoire',
        ];

        foreach ($countries as $input => $expected) {
            $country = Country::factory()->create(['name' => $input]);
            $this->assertEquals($expected, $country->name_normalized);
        }
    }

    public function test_get_all_applies_filters_correctly()
    {
        // Create test data
        Country::factory()->create([
            'name' => 'Nigeria',
            'region' => 'Africa',
            'currency_code' => 'NGN',
            'estimated_gdp' => 1000000
        ]);

        Country::factory()->create([
            'name' => 'Ghana',
            'region' => 'Africa',
            'currency_code' => 'GHS',
            'estimated_gdp' => 500000
        ]);

        Country::factory()->create([
            'name' => 'United States',
            'region' => 'Americas',
            'currency_code' => 'USD',
            'estimated_gdp' => 2000000
        ]);

        // Test region filter
        $result = $this->service->getAll(['region' => 'Africa']);
        $this->assertCount(2, $result);
        $this->assertEquals(['Nigeria', 'Ghana'], collect($result)->pluck('name')->toArray());

        // Test currency filter
        $result = $this->service->getAll(['currency' => 'NGN']);
        $this->assertCount(1, $result);
        $this->assertEquals('Nigeria', $result[0]['name']);

        // Test GDP sorting
        $result = $this->service->getAll(['sort' => 'gdp_desc']);
        $this->assertEquals(['United States', 'Nigeria', 'Ghana'], collect($result)->pluck('name')->toArray());
    }

    public function test_refresh_handles_api_errors()
    {
        Http::fake([
            'restcountries.com/*' => Http::response([], 500),
            'open.er-api.com/*' => Http::response([], 200)
        ]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('External data source unavailable');
        
        $this->service->refreshAll();
    }

    public function test_refresh_handles_invalid_country_data()
    {
        Http::fake([
            'restcountries.com/*' => Http::response([
                [
                    // Missing required name
                    'capital' => 'Invalid City',
                    'population' => 1000
                ]
            ]),
            'open.er-api.com/*' => Http::response(['rates' => []])
        ]);

        $result = $this->service->refreshAll();
        
        $this->assertEquals(0, Country::count());
        $this->assertArrayHasKey('total_countries', $result);
    }

    public function test_cache_invalidation_on_refresh()
    {
        Cache::shouldReceive('forget')->once()->with('countries_status');
        Cache::shouldReceive('tags')->once()->with('countries')->andReturnSelf();
        Cache::shouldReceive('flush')->once();

        Http::fake([
            'restcountries.com/*' => Http::response([]),
            'open.er-api.com/*' => Http::response(['rates' => []])
        ]);

        $this->service->refreshAll();
    }
}