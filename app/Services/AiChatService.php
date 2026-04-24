<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiChatService
{
    public function sendMessage($userMessage)
    {
        try {
            $apiKey = env('GEMINI_API_KEY');

            $systemPrompt = "You are Airé, a supportive mental wellness AI. Keep responses short, kind, and safe.";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$apiKey}",
                [
                    "contents" => [
                        [
                            "role" => "user",
                            "parts" => [
                                ["text" => $systemPrompt . "\n\nUser: " . $userMessage]
                            ]
                        ]
                    ]
                ]
            );

            // ✅ Check if request failed
            if (!$response->successful()) {
                return "🌸 I'm having trouble responding right now. Please try again 💚";
            }

            $data = $response->json();

            return $data['candidates'][0]['content']['parts'][0]['text']
                ?? "🌸 I'm here for you. Tell me more 💚";

        } catch (\Exception $e) {
            // ✅ Catch any crash
            return "🌸 Something went wrong. Please try again 💚";
        }
    }
}