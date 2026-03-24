<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/chat', 'chat');
Route::view('/products', 'products');
