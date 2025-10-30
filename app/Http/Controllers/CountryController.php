<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CountryController extends Controller
{
    public function __construct(
        private CountryService $countryService
    ) {}

    /**
     * Refresh all countries from external APIs
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $result = $this->countryService->refreshAll();
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'External data source unavailable. No data was changed.',
                'details' => 'External data source unavailable',
            ], 503);
        }
    }

    /**
     * Get all countries with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['region', 'currency', 'sort']);
        
        // Validate sort parameter
        if (isset($filters['sort']) && !in_array($filters['sort'], ['gdp_desc', 'gdp_asc', 'population_desc', 'population_asc'])) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => ['sort' => 'Invalid sort parameter']
            ], 400);
        }
        
        $countries = $this->countryService->getAll($filters);
        return response()->json($countries, 200);
    }

    /**
     * Get a specific country by name
     */
    public function show(string $name): JsonResponse
    {
        $country = $this->countryService->getByName($name);

        if (!$country) {
            return response()->json([
                'error' => 'Country not found',
            ], 404);
        }

        return response()->json($country, 200);
    }

    /**
     * Delete a country by name
     */
    public function destroy(string $name): JsonResponse
    {
        $deleted = $this->countryService->deleteByName($name);

        if (!$deleted) {
            return response()->json([
                'error' => 'Country not found',
            ], 404);
        }

        return response()->json(['message' => 'Country deleted successfully'], 200);
    }

    /**
     * Get status information
     */
    public function status(): JsonResponse
    {
        $status = $this->countryService->getStatus();
        return response()->json($status, 200);
    }

    /**
     * Serve the summary image
     */
    public function image(): JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $summaryPath = 'cache/summary.png';

        if (!Storage::disk('public')->exists($summaryPath)) {
            return response()->json([
                'error' => 'Summary image not found',
            ], 404);
        }

        $fullPath = Storage::disk('public')->path($summaryPath);
        return response()->file($fullPath, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300'
        ]);
    }
}


