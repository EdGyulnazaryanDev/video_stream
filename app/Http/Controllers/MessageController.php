<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\NewMessage;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $message = $request->input('message');
        $user = Auth::user();

        broadcast(new MessageSent($user, $message))->toOthers();

        return response()->json([
            'status' => 'Message Sent!',
            'message' => $message
        ]);
    }
}
