<?php

use App\Http\Controllers\Api\ChatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('conversations', ChatController::class)
    ->only(['index', 'store', 'show', 'destroy']);

Route::post('conversations/{conversationId}/messages', [ChatController::class, 'sendMessage'])
    ->name('conversations.messages.store');
