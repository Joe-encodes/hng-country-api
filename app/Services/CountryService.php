<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CountryService
{
    /**
     * Fetch all countries from external API and update/insert them
     */
    public function refreshAll(): array
    {
        try {
            // Fetch countries from restcountries API
            $countriesResponse = Http::timeout(10)
                ->get('https://restcountries.com/v2/all?fields=name,capital,region,population,flag,currencies');

            if (!$countriesResponse->successful()) {
                throw new \Exception('Failed to fetch countries from restcountries API');
            }

            // Fetch exchange rates from open.er-api
            $ratesResponse = Http::timeout(10)
                ->get('https://open.er-api.com/v6/latest/USD');

            if (!$ratesResponse->successful()) {
                throw new \Exception('Failed to fetch exchange rates from open.er-api');
            }

            $countries = $countriesResponse->json();
            $rates = $ratesResponse->json()['rates'] ?? [];

            DB::beginTransaction();

            try {
                $now = now();

                foreach ($countries as $countryData) {
                    // Validate required fields
                    if (empty($countryData['name']) || !isset($countryData['population'])) {
                        Log::warning('Skipping country missing name/population', $countryData);
                        continue;
                    }

                    $name = trim($countryData['name']);
                    $nameNormalized = mb_strtolower($name);
                    $capital = $countryData['capital'] ?? null;
                    $region = $countryData['region'] ?? null;
                    $population = (int) $countryData['population'];
                    $flag = $countryData['flag'] ?? null;

                    $currencies = $countryData['currencies'] ?? [];
                    $currencyCode = null;
                    $exchangeRate = null;
                    $estimatedGdp = null;

                    if (!empty($currencies) && is_array($currencies)) {
                        // Get first currency code
                        $firstCurrency = $currencies[0] ?? null;
                        $currencyCode = $firstCurrency['code'] ?? null;

                        if ($currencyCode && isset($rates[$currencyCode])) {
                            $exchangeRate = (float) $rates[$currencyCode];
                            // Calculate estimated_gdp with random multiplier
                            $randomMultiplier = rand(1000, 2000);
                            $estimatedGdp = ($population * $randomMultiplier) / $exchangeRate;
                        }
                    }

                    // If currency array is empty, set estimated_gdp to 0
                    if (empty($currencies)) {
                        $estimatedGdp = 0;
                    }

                    Country::updateOrCreate(
                        ['name_normalized' => $nameNormalized],
                        [
                            'name' => $name,
                            'capital' => $capital,
                            'region' => $region,
                            'population' => $population,
                            'currency_code' => $currencyCode,
                            'exchange_rate' => $exchangeRate,
                            'estimated_gdp' => $estimatedGdp,
                            'flag_url' => $flag,
                            'last_refreshed_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }

                DB::commit();

                // Generate summary image
                $this->generateSummaryImage($now);

                return [
                    'total_countries' => Country::count(),
                    'last_refreshed_at' => $now->toIso8601String(),
                ];
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Refresh failed during database operations', ['error' => $e->getMessage()]);
                throw $e;
            }
        } catch (\Throwable $e) {
            Log::error('External API unavailable', ['error' => $e->getMessage()]);
            throw new \Exception('External data source unavailable', 503);
        }
    }

    /**
     * Generate summary image with top 5 countries by GDP
     */
    protected function generateSummaryImage($timestamp): void
    {
        try {
            // Get top 5 countries by estimated GDP
            $topCountries = Country::whereNotNull('estimated_gdp')
                ->orderBy('estimated_gdp', 'desc')
                ->limit(5)
                ->get(['name', 'estimated_gdp']);

            $totalCountries = Country::count();

            // Create image using GD
            $width = 800;
            $height = 600;
            $image = imagecreate($width, $height);

            // Colors
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            $blue = imagecolorallocate($image, 0, 100, 200);

            // Clear with white background
            imagefill($image, 0, 0, $white);

            // Title
            $title = 'Countries Summary';
            $fontSize = 5;
            $titleX = ($width - (strlen($title) * imagefontwidth($fontSize))) / 2;
            imagestring($image, 5, $titleX, 50, $title, $blue);

            // Total countries
            $totalText = "Total Countries: {$totalCountries}";
            $textX = ($width - (strlen($totalText) * imagefontwidth($fontSize))) / 2;
            imagestring($image, 5, $textX, 100, $totalText, $black);

            // Top 5 countries
            $y = 150;
            $rank = 1;
            foreach ($topCountries as $country) {
                $formattedGdp = number_format($country->estimated_gdp ?? 0, 2);
                $text = "{$rank}. {$country->name} - $" . $formattedGdp;
                imagestring($image, 4, 100, $y, $text, $black);
                $y += 30;
                $rank++;
            }

            // Timestamp
            $timeText = "Last Refresh: " . $timestamp->format('Y-m-d H:i:s');
            $timeX = ($width - (strlen($timeText) * imagefontwidth($fontSize))) / 2;
            imagestring($image, 4, $timeX, $height - 50, $timeText, $blue);

            // Save image
            $summaryPath = 'cache/summary.png';
            Storage::disk('public')->makeDirectory('cache');
            $fullPath = Storage::disk('public')->path($summaryPath);
            
            imagepng($image, $fullPath);
            imagedestroy($image);
        } catch (\Throwable $e) {
            Log::warning('Failed to generate summary image', ['error' => $e->getMessage()]);
            // Don't throw - refresh can succeed even if image generation fails
        }
    }

    /**
     * Get all countries with filters and sorting
     */
    public function getAll(array $filters = []): array
    {
        $query = Country::query();

        // Apply filters
        if (!empty($filters['region'])) {
            $query->where('region', $filters['region']);
        }

        if (!empty($filters['currency'])) {
            $query->where('currency_code', mb_strtoupper($filters['currency']));
        }

        // Apply sorting
        if (!empty($filters['sort'])) {
            $sortMap = [
                'gdp_desc' => ['estimated_gdp', 'desc'],
                'gdp_asc' => ['estimated_gdp', 'asc'],
                'population_desc' => ['population', 'desc'],
                'population_asc' => ['population', 'asc'],
            ];

            if (isset($sortMap[$filters['sort']])) {
                [$field, $direction] = $sortMap[$filters['sort']];
                $query->orderBy($field, $direction);
            }
        }

        $countries = $query->get();

        return [
            'data' => $countries,
            'count' => $countries->count(),
            'filters_applied' => $filters,
        ];
    }

    /**
     * Get country by name
     */
    public function getByName(string $name): ?Country
    {
        $nameNormalized = mb_strtolower(trim($name));
        return Country::where('name_normalized', $nameNormalized)->first();
    }

    /**
     * Delete country by name
     */
    public function deleteByName(string $name): bool
    {
        $nameNormalized = mb_strtolower(trim($name));
        return Country::where('name_normalized', $nameNormalized)->delete();
    }

    /**
     * Get status information
     */
    public function getStatus(): array
    {
        $lastRefreshed = Country::max('last_refreshed_at');

        return [
            'total_countries' => Country::count(),
            'last_refreshed_at' => $lastRefreshed ? $lastRefreshed->toIso8601String() : null,
        ];
    }
}

