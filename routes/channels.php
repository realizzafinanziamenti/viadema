<?php

use Illuminate\Support\Facades\Broadcast;

require_once base_path('vendor/namu/wirechat/routes/channels.php');

Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
