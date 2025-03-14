<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('messages.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;  // Authorize user only if they match the userId in the channel
});
