<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiChatService;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $ai;

    public function __construct(AiChatService $ai)
    {
        $this->ai = $ai;
    }

    public function send(Request $request)
    {
        try {
            $message = $request->input('message');
            $sessionId = $request->input('session_id'); // NEW

            $reply = $this->ai->sendMessage($message);

            // ✅ SAVE USER MESSAGE
            Chat::create([
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'message' => $message,
                'is_user_message' => true,
            ]);

            // ✅ SAVE AI RESPONSE
            Chat::create([
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'message' => $message,
                'response' => $reply,
                'is_user_message' => false,
            ]);

            return response()->json([
                'reply' => $reply
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'reply' => '❌ ERROR: ' . $e->getMessage()
            ]);
        }
    }

}