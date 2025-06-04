<?php

namespace App\Http\Controllers;

use App\Services\AgoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AgoraController extends Controller
{

    public function join(Request $request)
    {
        if (!auth()->user()) {
            return redirect()->route('login');
        }
        $agora = new AgoraService();
        $user = auth()->user();
        $uid = $user->uid;
        $name = $user->name;
        $channelName = $request->get('channelName', 'testChannel');
        $token = $agora->generateToken($channelName, $uid);
        $appId = env('AGORA_APP_ID');
        $chatToken = env('AGORA_CHAT_APP_TOKEN');
        return view('call', compact('uid', 'name', 'token', 'appId', 'chatToken'));
    }
    public function generateToken(Request $request)
    {
        try {
            $channelName = $request->get('channelName', 'testChannel');
            $uid = auth()->user()?->uid;

            $agora = new AgoraService();
            $token = $agora->generateToken($channelName, $uid);

            return response()->json([
                'uid' => $uid,
                'token' => $token,
                'appId' => config('agora.app_id'),9
            ]);
        } catch (\Throwable $throwable) {
            Log::error($throwable);
        }
    }
}
