<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Translation\MessageCatalogue;

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/', [MessageController::class, 'index'])->name('home');
    Route::get('/inbox/{id}', [MessageController::class, 'inbox'])->name('inbox');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/messages/typing', [MessageController::class, 'typing'])->name('messages.typing');
    Route::post('/messages/stopTyping', [MessageController::class, 'stopTyping'])->name('messages.stopTyping');
});
