<?php

use App\Http\Controllers\Api\PlaywrightRunIngestController;
use Illuminate\Support\Facades\Route;

Route::post('playwright/runs', [PlaywrightRunIngestController::class, 'store'])
    ->middleware('playwright.token')
    ->name('api.playwright.runs.store');

