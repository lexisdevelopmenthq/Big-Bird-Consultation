<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('online-channel', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});

Broadcast::channel('online', function (User $user) {
    return  $user;
});
