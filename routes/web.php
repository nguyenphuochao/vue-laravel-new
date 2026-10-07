<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route web
Route::get('/', function () {
    $user = auth()->user();
    return view('products', compact("user"));
})->middleware('auth');

Auth::routes();

// API products
Route::resource('products', ProductController::class)->middleware('auth');
