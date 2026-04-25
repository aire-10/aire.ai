<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiChatService
{
    public function sendMessage($userMessage)
    {
        try {
            $apiKey = env('GEMINI_API_KEY');

            // ✅ CRISIS DETECTION - HIGHEST PRIORITY
            $userMessageLower = strtolower($userMessage);
            
            $crisisKeywords = [
                'suicide', 'kill myself', 'end my life', 'want to die', 
                'hurt myself', 'self harm', 'jump off', 'jump off a bridge',
                'die', 'dying', 'take my life', 'end it all', 'better off dead'
            ];
            
            foreach ($crisisKeywords as $keyword) {
                if (strpos($userMessageLower, $keyword) !== false) {
                    return "💚 I hear that you're going through an extremely difficult time right now.\n\n" .
                           "Your feelings are valid, and you don't have to go through this alone.\n\n" .
                           "📞 **Please reach out for immediate support:**\n" .
                           "• **Talian Harapan:** 145 (24/7 crisis support)\n" .
                           "• **Emergency:** 991\n\n" .
                           "You matter. You are not alone. Please call them right now. 💚";
                }
            }

            // ✅ STRESS DETECTION - suggest self-care tools
            $stressKeywords = [
                'stressed', 'stress', 'overwhelmed', 'burnout', 'pressure',
                'anxious', 'anxiety', 'worried', 'nervous', 'panic',
                'tired', 'exhausted', 'drained', 'burnt out',
                'tertekan', 'cemas', 'letih', 'penat'
            ];
            
            $isStressRelated = false;
            foreach ($stressKeywords as $keyword) {
                if (strpos($userMessageLower, $keyword) !== false) {
                    $isStressRelated = true;
                    break;
                }
            }

            // ✅ YOUR DETAILED SYSTEM PROMPT
            $systemPrompt = "You are Airé, a compassionate mental wellness AI companion. 
Keep responses short (2-4 sentences). Use emojis occasionally 💚🦋
Be warm, kind, and supportive. Never give medical advice.

IMPORTANT RULES:
- If user mentions suicide/self-harm → Provide Talian Harapan: 145 and Emergency: 991
- If user mentions stress/anxiety/tired → Suggest self-care tools (breathing exercises, grounding, mood booster)

YOUR PRIMARY ROLES:
1. Be a space to express emotions freely
2. Provide guidance during difficult moments
3. Help users understand their feelings better
4. Offer light support before professional help

HOW TO RESPOND:
- First acknowledge and validate feelings before offering help
- For stressed users: Suggest trying self-care features like:
  * Breathing exercises (/breathing-mt)
  * Grounding exercise (/grounding)
  * Mood booster (/moodbooster)
  * Mini tasks (/minitask)
  * Mind reset (/mindreset)

CRISIS NUMBERS (provide immediately when needed):
- Talian Harapan: 145
- Emergency: 991

SELF-CARE TOOLS TO SUGGEST (for stress/anxiety/tiredness):
- 🌬️ Breathing exercises - calm your mind in minutes
- 🌿 Grounding exercise - 5-4-3-2-1 technique
- ⚡ Mood booster - quick activities to lift your mood
- ✅ Mini tasks - small steps, big difference
- 🧘 Mind reset - clear mental fog

LANGUAGE STYLE:
- Be casual like a close friend
- Mix Malay and English naturally (e.g., 'Macam mana perasaan awda today?')

REMEMBER: You are a supportive companion, NOT a licensed therapist. Always encourage professional help for serious concerns.";

            // Add stress-specific instruction if detected
            $stressInstruction = "";
            if ($isStressRelated) {
                $stressInstruction = "\n\nIMPORTANT: The user is showing signs of stress. Please acknowledge their feeling and suggest trying one of our self-care features (like breathing exercises, grounding, or mood booster) to help them feel better.";
            }

            // Send to Gemini API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}",
                [
                    "contents" => [
                        [
                            "role" => "user",
                            "parts" => [
                                ["text" => $systemPrompt . $stressInstruction . "\n\nUser message: " . $userMessage]
                            ]
                        ]
                    ],
                    "generationConfig" => [
                        "temperature" => 0.7,
                        "maxOutputTokens" => 250,
                        "topP" => 0.9
                    ]
                ]
            );

            // Check if request failed
            if (!$response->successful()) {
                \Log::error('Gemini API Error: ' . $response->body());
                // Fallback response with self-care suggestion for stress
                if ($isStressRelated) {
                    return "🌸 I hear you're going through a tough time. Would you like to try a quick breathing exercise or grounding technique? You can find them in our self-care tools. 💚";
                }
                return "🌸 I'm here for you. Could you tell me a bit more? 💚";
            }

            $data = $response->json();

            // Extract response
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($reply) {
                // Add self-care suggestion if stress detected and not already in response
                if ($isStressRelated && !str_contains(strtolower($reply), 'breath') && !str_contains(strtolower($reply), 'grounding') && !str_contains(strtolower($reply), 'self-care')) {
                    $reply .= "\n\n🌿 Would you like to try a quick self-care exercise? Check out our **Breathing** or **Grounding** tools in the self-care section. 💚";
                }
                return $reply;
            }

            if ($isStressRelated) {
                return "🌸 It sounds like you're carrying a lot. Remember to be gentle with yourself. Would you like to try a quick breathing exercise? 🌿";
            }
            
            return "🌸 I'm listening. What's on your mind today? 🦋";

        } catch (\Exception $e) {
            \Log::error('Chat Error: ' . $e->getMessage());
            return "🌸 Let's take a gentle breath together. I'm here for you. 💚";
        }
    }
}