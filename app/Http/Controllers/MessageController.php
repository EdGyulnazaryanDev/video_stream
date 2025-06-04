<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\NewMessage;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
//    public function sendMessage(Request $request): \Illuminate\Http\JsonResponse
//    {
//        $message = $request->input('message');
//        broadcast(new MessageSent($message))->toOthers();
//        // event(new MessageSent($message));
//        return response()->json(['status' => 'Message sent!', 'message' => $message]);
//    }

    public function sendMessage(Request $request)
    {
        $message = $request->input('message');
//        event(new NewMessage($request->message, auth()->user()->name));
        event(new MessageSent(auth()->user(), $message));
        return ['status' => 'Message Sent!', $message];
    }
}
