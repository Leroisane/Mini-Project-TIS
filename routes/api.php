<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
Route::patch('/events/{id}/reduce-quota', [EventController::class, 'reduceQuota']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
