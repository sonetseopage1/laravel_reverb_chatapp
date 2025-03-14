<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Translation\MessageCatalogue;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('message.index');
    Route::get('/inbox/{id}', [MessageController::class, 'inbox'])->name('inbox');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
});
