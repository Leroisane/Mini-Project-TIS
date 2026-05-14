<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::post('/payments', [PaymentController::class, 'store']);
Route::post('/payments/simulate', [PaymentController::class, 'simulate']);
