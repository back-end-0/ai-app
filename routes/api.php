<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('conversations', ChatController::class)
    ->only(['index', 'store', 'show', 'destroy']);

Route::post('conversations/{conversationId}/messages', [ChatController::class, 'sendMessage'])
    ->name('conversations.messages.store');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
Route::post('products/{product}/generate-description', [ProductController::class, 'generateDescription'])
    ->name('products.generate-description');
Route::post('products/{product}/translate', [ProductController::class, 'translate'])
    ->name('products.translate');
