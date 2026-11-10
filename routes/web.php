<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/books')->name('dashboard');
    Route::resource('books', BookController::class)->except('show');
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
});

require __DIR__.'/settings.php';
