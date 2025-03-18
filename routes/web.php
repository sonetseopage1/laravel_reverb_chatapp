<?php

use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoCallController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Translation\MessageCatalogue;

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/', [MessageController::class, 'index']);
    Route::get('/home', [MessageController::class, 'index'])->name('home');
    Route::get('/inbox/{id}', [MessageController::class, 'inbox'])->name('inbox');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/messages/typing', [MessageController::class, 'typing'])->name('messages.typing');
    Route::post('/messages/stopTyping', [MessageController::class, 'stopTyping'])->name('messages.stopTyping');

    Route::get('/message_inbox/{id}', [MessageController::class, 'message_inbox'])->name('message_inbox');
    Route::get('/chat/load-more', [MessageController::class, 'loadMoreMessages'])->name('chat.loadMore');

    Route::get('/video/call', [VideoCallController::class, 'call']);
    Route::post('/video/signal', [VideoCallController::class, 'signal'])->name('video_signal');
});
