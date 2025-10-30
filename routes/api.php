<?php

use App\Http\Controllers\CountryController;
use Illuminate\Support\Facades\Route;

// Country Currency & Exchange API Routes
Route::post('/countries/refresh', [CountryController::class, 'refresh']);
Route::get('/countries/image', [CountryController::class, 'image']);
Route::get('/countries/{name}', [CountryController::class, 'show']);
Route::delete('/countries/{name}', [CountryController::class, 'destroy']);
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/status', [CountryController::class, 'status']);


