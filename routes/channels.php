<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('stream_channel', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
