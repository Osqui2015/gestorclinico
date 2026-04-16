<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('doctor.{doctorId}', function ($user, int $doctorId): bool {
    return (int) $user->id === $doctorId;
});
