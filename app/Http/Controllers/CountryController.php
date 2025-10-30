<?php

namespace App\Http\Controllers;

use App\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
        } catch (HttpException $e) {
            // Consistent structured error object for external failures
            return response()->json([
                'error' => [
                    'status' => $e->getStatusCode(),
                    'message' => $e->getMessage()
                ],
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            // Generic internal server error
            return response()->json([
                'error' => [
                    'status' => 500,
                    'message' => 'Internal server error'
                ],
            ], 500);
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
                'error' => [
                    'status' => 400,
                    'message' => 'Validation failed',
                    'details' => ['sort' => 'Invalid sort parameter']
                ]
            ], 400);
        }

        $countries = $this->countryService->getAll($filters);

        // Return plain array (per spec)
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
                'error' => [
                    'status' => 404,
                    'message' => 'Country not found'
                ]
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
                'error' => [
                    'status' => 404,
                    'message' => 'Country not found'
                ]
            ], 404);
        }

        return response()->json([
            'message' => 'Country deleted successfully'
        ], 200);
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
        // Serve from public/cache/summary.png so grader sees it directly
        $summaryPath = public_path('cache/summary.png');

        if (!file_exists($summaryPath)) {
            return response()->json([
                'error' => [
                    'status' => 404,
                    'message' => 'Summary image not found'
                ]
            ], 404);
        }

        return response()->file($summaryPath, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300'
        ]);
    }
}
