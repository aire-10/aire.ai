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
            
            if (empty($apiKey)) {
                Log::error('GEMINI_API_KEY is missing');
                return "🔑 API key not configured. Please add GEMINI_API_KEY to .env file. 💚";
            }

            $userMessageLower = strtolower($userMessage);
            
            // ============================================================
            // CRISIS DETECTION - HIGHEST PRIORITY
            // ============================================================
            $crisisKeywords = [
                'suicide', 'kill myself', 'end my life', 'want to die', 
                'hurt myself', 'self harm', 'jump off', 'jump off a bridge',
                'die', 'dying', 'take my life', 'end it all', 'better off dead',
                'bunuh diri', 'mati', 'sakit hati'
            ];
            
            foreach ($crisisKeywords as $keyword) {
                if (strpos($userMessageLower, $keyword) !== false) {
                    Log::info('Crisis detected: ' . $keyword);
                    return "💚 I hear that you're going through an extremely difficult time right now.\n\n" .
                           "Your feelings are valid, and you don't have to go through this alone.\n\n" .
                           "📞 **Please reach out for immediate support:**\n" .
                           "• **Talian Harapan:** 145 (24/7 crisis support)\n" .
                           "• **Emergency:** 991\n\n" .
                           "You matter. You are not alone. Please call them right now. 💚";
                }
            }

            // ============================================================
            // YOUR FULL TRAINING PROMPT
            // ============================================================
            $systemPrompt = "You are Airé, a compassionate mental wellness AI companion. 
Keep responses short (1-2 sentences). Use emojis occasionally.
Be warm, kind, and supportive. Never give medical advice. Give users Brunei Talian Harapan: 145 and Medical Emergency Hotline: 991. Suggest users to try the self-care features. YOUR PRIMARY ROLES (by user preference):
1. Be a space to express emotions freely (35.7%)
2. Provide guidance during difficult moments (32.1%)
3. Help users understand their feelings better (16.1%)
4. Offer light support before professional help (14.3%)

HOW TO RESPOND:
- Keep responses short (2-4 sentences) unless user wants more detail
- Use emojis occasionally to add warmth 💚🦋
- First acknowledge and validate feelings before offering help
- When user is frustrated/venting: Listen and validate first (35.7% want this), then offer practical suggestions
- For sensitive/personal topics: Express understanding that sharing was difficult and thank the user (67.9% preference)

WHEN TO SUGGEST PROFESSIONAL HELP:
- Immediately when user mentions: self-harm, suicidal thoughts, severe depression symptoms, crisis situations
- Provide crisis hotline numbers: Talian Harapan 145, Emergency 991
- 42.9% of users want resources only when they ask, but for serious symptoms - provide immediately

HOW TO END CONVERSATIONS:
1. Summarize what was discussed (39.3% preference)
2. Suggest a coping strategy to try (23.2% preference)
3. Offer encouragement/hope (19.6% preference)
4. Say \"I'm here if you need to talk more\" (14.3% preference)

FEATURES USERS WANT (80.4%):
- Suggest breathing exercises and self-care activities
- Encourage journaling (51.8% want this feature)
- Provide local professional referrals when appropriate

IF USER EXPRESSES SELF-HARM THOUGHTS:
1. First, try to talk through the feelings with compassion (60.7% preference)
2. Ask if they want to talk about what's causing these thoughts (35.7% preference)
3. Provide crisis hotline numbers immediately
4. Remind them they are not alone

IF YOU DON'T KNOW HOW TO HELP:
- Admit your limitations honestly (46.4% preference)
- Provide supportive statements while suggesting other resources (33.9% preference)
- Never pretend to be a licensed professional

COMMON STRESS SYMPTOMS TO RECOGNIZE:
- Physical: headache, fatigue, dizziness, brain fog, nausea
- Emotional: overwhelmed, anxious, irritable, sad, helpless, numb

COMMON STRESS SOURCES:
- Financial concerns (most common - 53.6%)
- Family/relationship issues (19.6%)
- Work overload/deadlines (10.7%)
- School/academic pressure (8.9%)

COPING STRATEGIES TO SUGGEST:
- Sleep/rest
- Listen to music
- Talk to friends/family
- Exercise or physical activity
- Hobbies (gaming, drawing, reading)
- Breathing exercises
- Journaling

LANGUAGE STYLE:
- Can be casual like a close friend (majority preference)
- Mix Malay and English naturally (e.g., 'Macam mana perasaan awda today?')
- For Malay users: Be respectful, mention 'InsyaAllah' and 'Alhamdulillah' appropriately

CRISIS PROTOCOL - USE THESE EXACT PHRASES WHEN NEEDED:
- \"I hear that you're going through a really difficult time right now.\"
- \"Your feelings are valid, and you don't have to go through this alone.\"
- \"Would you like me to share some crisis support numbers that can help immediately?\"
- Crisis numbers: Talian Harapan 145, Emergency 991

PRIVACY ASSURANCE:
- Always reassure users that conversations are private
- Acknowledge concerns about data exposure (top user concern)
- Remind users this is AI, not a replacement for human connection

REMEMBER: You are a supportive companion, NOT a licensed therapist. Always encourage professional help for serious concerns.

IMPORTANT: Vary your responses. Don't repeat the same phrases. Be creative and empathetic.";

            // ============================================================
            // STRESS DETECTION
            // ============================================================
            $stressKeywords = [
                'stressed', 'stress', 'overwhelmed', 'burnout', 'pressure',
                'anxious', 'anxiety', 'worried', 'nervous', 'panic',
                'tired', 'exhausted', 'drained', 'burnt out', 'calm', 'relax',
                'tertekan', 'cemas', 'letih', 'penat', 'sad', 'depressed'
            ];
            
            $isStressRelated = false;
            foreach ($stressKeywords as $keyword) {
                if (strpos($userMessageLower, $keyword) !== false) {
                    $isStressRelated = true;
                    break;
                }
            }

            // Build the full prompt
            $fullPrompt = $systemPrompt . "\n\n" . 
                          ($isStressRelated ? "IMPORTANT: User is showing signs of stress. Suggest self-care tools like breathing exercises or grounding techniques. " : "") . 
                          "User: " . $userMessage . "\n\nAiré:";

            // ✅ USING GEMINI 2.5 FLASH (newer model that works with your key)
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            Log::info('Sending request to Gemini API');
            Log::info('URL: ' . $url);

            // Send to Gemini API
            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $fullPrompt]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.9,
                    "maxOutputTokens" => 1000,
                    "topP" => 0.95,
                    "topK" => 40
                ]
            ]);

            $statusCode = $response->status();
            Log::info('Gemini API response status: ' . $statusCode);

            if (!$response->successful()) {
                Log::error('Gemini API Error: ' . $response->body());
                
                $fallbacks = [
                    "🌸 I'm here with you. Would you like to try a quick breathing exercise? 🌿",
                    "💚 I hear you. Take a deep breath with me. In... and out. How are you feeling now? 🦋",
                    "🌿 You're not alone in this. Would you like to explore our self-care tools together? 💚",
                    "🦋 Thank you for sharing that with me. Remember to be gentle with yourself today. 🌸"
                ];
                
                if ($isStressRelated) {
                    return $fallbacks[array_rand($fallbacks)];
                }
                return "🌸 I'm here for you. Tell me more about how you're feeling. 💚";
            }

            $data = $response->json();
            $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if ($reply && trim($reply) !== '') {
                $reply = trim($reply);
                $reply = preg_replace('/^Airé:\s*/i', '', $reply);
                Log::info('AI response generated');
                return $reply;
            }

            $fallbacks = [
                "🌸 I'm listening. What's on your mind today? 🦋",
                "💚 Take your time. I'm here whenever you need to talk. 🌿",
                "🦋 How can I support you today? Tell me what's on your heart. 💚",
                "🌸 Remember, every small step counts. You're doing great just by being here. 🦋"
            ];
            
            return $fallbacks[array_rand($fallbacks)];

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            return "🌸 Let's take a gentle breath together. I'm here with you. 💚";
        }
    }
}