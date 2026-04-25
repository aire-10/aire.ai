<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    public function sendMessage($userMessage)
    {
        try {
            $apiKey = env('GEMINI_API_KEY');
            
            // Log that we received a message
            Log::info('Chat message received: ' . substr($userMessage, 0, 100));

            // ✅ CRISIS DETECTION - HIGHEST PRIORITY
            $userMessageLower = strtolower($userMessage);
            
            $crisisKeywords = [
                'suicide', 'kill myself', 'end my life', 'want to die', 
                'hurt myself', 'self harm', 'jump off', 'jump off a bridge',
                'die', 'dying', 'take my life', 'end it all', 'better off dead'
            ];
            
            foreach ($crisisKeywords as $keyword) {
                if (strpos($userMessageLower, $keyword) !== false) {
                    Log::info('Crisis detected: ' . $keyword);
                    return "💚 I hear that you're going through an extremely difficult time right now.\n\n" .
                           "Your feelings are valid, and you don't have to go through this alone.\n\n" .
                           "📞 Please reach out for immediate support:\n" .
                           "• Talian Harapan: 145 (24/7 crisis support)\n" .
                           "• Emergency: 991\n\n" .
                           "You matter. You are not alone. Please call them right now. 💚";
                }
            }

            // ✅ STRESS DETECTION - suggest self-care tools
            $stressKeywords = [
                'stressed', 'stress', 'overwhelmed', 'burnout', 'pressure',
                'anxious', 'anxiety', 'worried', 'nervous', 'panic',
                'tired', 'exhausted', 'drained', 'burnt out', 'calm', 'relax',
                'tertekan', 'cemas', 'letih', 'penat'
            ];
            
            $isStressRelated = false;
            foreach ($stressKeywords as $keyword) {
                if (strpos($userMessageLower, $keyword) !== false) {
                    $isStressRelated = true;
                    Log::info('Stress detected: ' . $keyword);
                    break;
                }
            }

            // ✅ SIMPLER SYSTEM PROMPT - More direct
            $systemPrompt = "You are Airé, a warm, caring mental wellness companion. Keep responses short (2-3 sentences).

RULES:
- If user mentions stress/anxiety/tired: Suggest breathing exercises or grounding techniques
- If user asks for suggestions: Give specific self-care activities
- Be conversational like a close friend

SELF-CARE TOOLS TO SUGGEST:
- 🌬️ Breathing exercise: Inhale 4 sec, hold 4 sec, exhale 6 sec
- 🌿 5-4-3-2-1 grounding: Name 5 things you see, 4 you feel, 3 you hear, 2 you smell, 1 you like
- ⚡ Quick mood booster: Listen to a favorite song or stretch for 2 minutes
- ✅ Small task: Drink water or take 5 deep breaths
- 🧘 Mind reset: Step away from screens for 5 minutes

Examples:
User: 'I'm stressed' → 'I hear you. Let's try a quick breathing exercise together. Inhale... 1, 2, 3, 4. Exhale... 🌬️'
User: 'What should I do?' → 'How about a 5-4-3-2-1 grounding exercise? Look around and name 5 things you can see right now. 🌿'
User: 'Need something to calm me' → 'I understand. Try taking 5 slow deep breaths. Breathe in calm, breathe out tension. Want me to guide you? 🦋'

Be warm and helpful. Never give medical advice. Use emojis occasionally 💚";

            // Build the full prompt
            $fullPrompt = $systemPrompt . "\n\n" . ($isStressRelated ? "IMPORTANT: User is showing signs of stress. Suggest self-care tools. " : "") . "User: " . $userMessage . "\n\nAiré:";

            Log::info('Sending request to Gemini API');

            // Send to Gemini API
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $fullPrompt]
                            ]
                        ]
                    ],
                    "generationConfig" => [
                        "temperature" => 0.8,
                        "maxOutputTokens" => 200,
                        "topP" => 0.9,
                        "topK" => 40
                    ]
                ]
            );

            // Log response status
            Log::info('Gemini API response status: ' . $response->status());

            // Check if request failed
            if (!$response->successful()) {
                Log::error('Gemini API Error: ' . $response->body());
                
                // Return appropriate fallback based on context
                if ($isStressRelated) {
                    return "🌸 I hear you're feeling overwhelmed. Try this: take 5 slow deep breaths. Breathe in... 1,2,3,4. Breathe out... 1,2,3,4. How do you feel? 🦋";
                }
                return "🌸 I'm here for you. Would you like to try a quick breathing exercise together? 🌿";
            }

            $data = $response->json();
            
            // Log the response for debugging
            Log::info('Gemini API response structure: ' . json_encode(array_keys($data)));

            // Extract response - different possible paths
            $reply = null;
            
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $reply = $data['candidates'][0]['content']['parts'][0]['text'];
            } elseif (isset($data['candidates'][0]['output'])) {
                $reply = $data['candidates'][0]['output'];
            } elseif (isset($data['reply'])) {
                $reply = $data['reply'];
            }

            if ($reply && trim($reply) !== '') {
                Log::info('AI response: ' . substr($reply, 0, 100));
                
                // Clean up the response if it repeats the user message
                $reply = trim($reply);
                
                // Remove any "Airé:" prefix if present
                $reply = preg_replace('/^Airé:\s*/i', '', $reply);
                
                return $reply;
            }

            Log::warning('Empty response from Gemini API');
            
            // Final fallback
            if ($isStressRelated) {
                return "🌸 Let's try something simple. Look around and name 5 things you can see right now. This grounding technique can help calm your mind. 🌿";
            }
            
            return "🌸 I'm listening. Tell me more about how you're feeling. 💚";

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            
            // Friendly fallback
            return "🌸 Let's take a gentle breath together. Inhale... exhale... I'm right here with you. What's on your mind? 💚";
        }
    }
}