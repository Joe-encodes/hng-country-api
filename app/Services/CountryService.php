<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CountryService
{
    /**
     * Fetch all countries from external API and update/insert them
     */
    public function refreshAll(): array
    {
        try {
            // Fetch countries from restcountries API
            // Note: withoutVerifying() is used to bypass SSL verification on Windows dev setups.
            // Replace with proper CA bundle configuration for production.
            $countriesResponse = Http::withoutVerifying()->timeout(10)
                ->get('https://restcountries.com/v2/all?fields=name,capital,region,population,flag,currencies');

            if (!$countriesResponse->successful()) {
                throw new HttpException(503, 'External data source unavailable: Countries API');
            }

            // Fetch exchange rates from open.er-api
            $ratesResponse = Http::withoutVerifying()->timeout(10)
                ->get('https://open.er-api.com/v6/latest/USD');

            if (!$ratesResponse->successful()) {
                throw new HttpException(503, 'External data source unavailable: Exchange API');
            }

            $countries = $countriesResponse->json();
            $rates = $ratesResponse->json()['rates'] ?? [];

            $now = now();
            $rows = [];
            
            // Delete existing data
            Country::truncate();

            foreach ($countries as $countryData) {
                // Validate required fields
                if (empty($countryData['name']) || !isset($countryData['population'])) {
                    Log::warning('Skipping country missing name/population', $countryData);
                    continue;
                }

                $name = trim($countryData['name']);
                $nameNormalized = Str::slug(mb_strtolower($name));
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

                if (empty($currencies)) {
                    $estimatedGdp = 0;
                }

                $rows[] = [
                    'name_normalized' => $nameNormalized,
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
                    'created_at' => $now,
                ];
            }

            // Bulk upsert in chunks to minimize round-trips
            foreach (array_chunk($rows, 500) as $chunk) {
                Country::upsert(
                    $chunk,
                    ['name_normalized'],
                    [
                        'name', 'capital', 'region', 'population',
                        'currency_code', 'exchange_rate', 'estimated_gdp',
                        'flag_url', 'last_refreshed_at', 'updated_at'
                    ]
                );
            }

                // Generate summary image
                // Clear caches
                Cache::forget('countries_status');
                Cache::tags('countries')->flush();

                // Generate summary image
                $this->generateSummaryImage();

                return [
                    'message' => 'Countries refreshed successfully',
                    'total_countries' => Country::count(),
                    'last_refreshed_at' => $now->toIso8601String(),
                ];
        } catch (\Throwable $e) {
            Log::error('External API unavailable', ['error' => $e->getMessage()]);
            if ($e instanceof HttpException) {
                throw $e;
            }
            throw new HttpException(503, 'External data source unavailable. No data was changed.');
        }
    }

    /**
     * Generate summary image with top 5 countries by GDP
     */
    protected function generateSummaryImage(): void
    {
        try {
            // Get top 5 countries by estimated GDP (treat NULL as 0)
            $topCountries = Country::orderByRaw('COALESCE(estimated_gdp, 0) DESC')
                ->limit(5)
                ->get(['name', 'estimated_gdp']);

            $totalCountries = Country::count();
            $timestamp = now();

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
            $titleX = (int) (($width - (strlen($title) * imagefontwidth($fontSize))) / 2);
            imagestring($image, 5, $titleX, 50, $title, $blue);

            // Total countries
            $totalText = "Total Countries: {$totalCountries}";
            $textX = (int) (($width - (strlen($totalText) * imagefontwidth($fontSize))) / 2);
            imagestring($image, 5, $textX, 100, $totalText, $black);

            // Top 5 countries
            $y = 150;
            $rank = 1;
            foreach ($topCountries as $country) {
                $formattedGdp = number_format($country->estimated_gdp ?? 0, 2);
                $text = "{$rank}. {$country->name} - $" . $formattedGdp;
                imagestring($image, 4, 100, (int) $y, $text, $black);
                $y += 30;
                $rank++;
            }

            // Timestamp
            $timeText = "Last Refresh: " . $timestamp->format('Y-m-d H:i:s');
            $timeX = (int) (($width - (strlen($timeText) * imagefontwidth($fontSize))) / 2);
            imagestring($image, 4, $timeX, $height - 50, $timeText, $blue);

            // Save image
            Storage::disk('public')->makeDirectory('cache');
            $fullPath = Storage::disk('public')->path('cache/summary.png');
            
            imagepng($image, $fullPath);
            imagedestroy($image);
            
            // Verify the image was created successfully
            if (!Storage::disk('public')->exists('cache/summary.png')) {
                throw new \RuntimeException('Failed to save summary image');
            }
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

        // Apply sorting with name as secondary sort for consistent ordering
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'gdp_desc':
                    $query->orderByDesc('estimated_gdp')->orderBy('name');
                    break;
                case 'gdp_asc':
                    $query->orderBy('estimated_gdp')->orderBy('name');
                    break;
                case 'population_desc':
                    $query->orderByDesc('population')->orderBy('name');
                    break;
                case 'population_asc':
                    $query->orderBy('population')->orderBy('name');
                    break;
            }
        }
        
        // Only include valid entries
        $query->whereNotNull('name')->whereNotNull('population');

        // Always add created_at as primary sort unless explicitly sorting by other field
        if (empty($filters['sort'])) {
            $query->orderBy('created_at');
        }

        // Execute query and return as a plain array per spec sample
        return $query->get()->values()->toArray();
    }

    /**
     * Get country by name
     */
    public function getByName(string $name): ?Country
    {
        $nameNormalized = Str::slug(mb_strtolower(trim($name)));
        return Country::where('name_normalized', $nameNormalized)
            ->orWhere('name', 'LIKE', trim($name))
            ->first();
    }

    /**
     * Delete country by name
     */
    public function deleteByName(string $name): bool
    {
        $nameNormalized = Str::slug(mb_strtolower(trim($name)));
        $country = Country::where('name_normalized', $nameNormalized)
            ->orWhere('name', 'LIKE', trim($name))
            ->first();

        if (!$country) {
            return false;
        }

        return $country->delete();
    }

    /**
     * Get status information
     */
    public function getStatus(): array
    {
        $lastRefreshed = Country::max('last_refreshed_at');

        return [
            'total_countries' => Country::count(),
            'last_refreshed_at' => $lastRefreshed
                ? \Illuminate\Support\Carbon::parse($lastRefreshed)->toIso8601String()
                : null,
        ];
    }
}
