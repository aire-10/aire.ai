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
        $message = $request->input('message');

        $reply = $this->ai->sendMessage($message);

        Chat::create([
            'user_id' => Auth::id(),
            'message' => $message,
            'reply' => $reply
        ]);

        return response()->json([
            'reply' => $reply
        ]);
    }
}