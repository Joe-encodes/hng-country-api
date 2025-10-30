<?php

namespace Tests\Performance;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Traits\WithCountryTestData;

class CountryApiPerformanceTest extends TestCase
{
    use RefreshDatabase, WithCountryTestData;

    public function test_countries_endpoint_response_time()
    {
        // Create 1000 test countries
        Country::factory()->count(1000)->create();

        $start = microtime(true);
        
        $response = $this->getJson('/api/countries');
        
        $time = microtime(true) - $start;
        
        $response->assertStatus(200);
        
        // Response should be under 500ms
        $this->assertLessThan(0.5, $time, "Response time was {$time} seconds");
    }

    public function test_refresh_endpoint_handles_large_dataset()
    {
        $this->mockSuccessfulExternalApis();

        // Track memory usage
        $startMemory = memory_get_usage();
        
        $response = $this->postJson('/api/countries/refresh');
        
        $peakMemory = memory_get_peak_usage() - $startMemory;
        
        $response->assertStatus(200);
        
        // Memory usage should be under 50MB
        $this->assertLessThan(50 * 1024 * 1024, $peakMemory, 
            "Peak memory usage was " . round($peakMemory / 1024 / 1024, 2) . "MB");
    }

    public function test_database_query_performance()
    {
        Country::factory()->count(1000)->create();

        DB::enableQueryLog();
        
        $response = $this->getJson('/api/countries?region=Africa&sort=gdp_desc');
        
        $queries = DB::getQueryLog();
        
        // Should use proper indexes and not generate excessive queries
        $this->assertLessThan(3, count($queries), 
            "Generated " . count($queries) . " queries, expected less than 3");
        
        // Check if we're using the correct indexes
        $explain = DB::select('EXPLAIN ' . str_replace(['?'], ['\'Africa\''], $queries[0]['query']));
        
        // Should use index for region and gdp sorting
        $this->assertStringContainsString('index', strtolower(json_encode($explain)));
    }
}