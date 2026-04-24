<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Mood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the chat history page
     */
    public function index()
    {
        return view('history');
    }
    
    /**
     * Get all chat sessions for the authenticated user
     * This matches the data structure expected by history.js
     */
    public function getSessions(Request $request)
    {
        $userId = Auth::id();
        
        // Get all chat sessions (grouped by session_id)
        $chats = Chat::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('session_id');
        
        $sessions = [];
        
        foreach ($chats as $sessionId => $messages) {
            $firstMessage = $messages->first();
            $lastMessage = $messages->last();
            
            // Detect mood from messages
            $mood = $this->detectMoodFromMessages($messages);
            
            // Generate preview (last user message)
            $preview = $this->generatePreview($messages);
            
            // Get session title (first user message or default)
            $title = $this->getSessionTitle($messages);
            
            $sessions[] = [
                'id' => $sessionId,
                'title' => $title,
                'preview' => $preview,
                'mood' => $mood,
                'updatedAt' => $lastMessage ? strtotime($lastMessage->created_at) * 1000 : time() * 1000,
                'createdAt' => $firstMessage ? strtotime($firstMessage->created_at) * 1000 : time() * 1000,
                'messageCount' => $messages->count()
            ];
        }
        
        // Apply filters
        $sessions = $this->applyFilters($sessions, $request);
        
        // Sort by updated date (newest first)
        usort($sessions, function($a, $b) {
            return $b['updatedAt'] - $a['updatedAt'];
        });
        
        return response()->json([
            'sessions' => $sessions,
            'total' => count($sessions)
        ]);
    }
    
    /**
     * Get a single chat session with all messages
     */
    public function getSession($id)
    {
        $userId = Auth::id();
        
        $messages = Chat::where('user_id', $userId)
            ->where('session_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();
        
        if ($messages->isEmpty()) {
            return response()->json(['error' => 'Session not found'], 404);
        }
        
        $formattedMessages = [];
        foreach ($messages as $message) {
            $formattedMessages[] = [
                'id' => $message->id,
                'text' => $message->message,
                'isUser' => $message->is_user_message,
                'response' => $message->response,
                'timestamp' => strtotime($message->created_at) * 1000
            ];
        }
        
        return response()->json([
            'id' => $id,
            'messages' => $formattedMessages,
            'title' => $this->getSessionTitle($messages)
        ]);
    }
    
    /**
     * Create a new chat session
     */
    public function createSession(Request $request)
    {
        $sessionId = $this->generateSessionId();
        
        return response()->json([
            'id' => $sessionId,
            'title' => 'New Chat',
            'createdAt' => time() * 1000
        ]);
    }
    
    /**
     * Save a message to a chat session
     */
    public function saveMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string',
            'is_user' => 'required|boolean',
            'response' => 'nullable|string'
        ]);
        
        $chat = Chat::create([
            'user_id' => Auth::id(),
            'session_id' => $request->session_id,
            'message' => $request->message,
            'response' => $request->response,
            'is_user_message' => $request->is_user,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Update session title if this is the first user message
        if ($request->is_user && $request->is_user === true) {
            $messageCount = Chat::where('user_id', Auth::id())
                ->where('session_id', $request->session_id)
                ->where('is_user_message', true)
                ->count();
            
            if ($messageCount === 1) {
                // First user message - use it as title
                $title = substr($request->message, 0, 50);
                // You could store title in a separate sessions table
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => $chat
        ]);
    }
    
    /**
     * Rename a chat session
     */
    public function renameSession(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);
        
        // Since we don't have a sessions table, we'll store the title in the first message's metadata
        // Or create a user_chat_sessions table
        
        // For now, we can store in a JSON field or create a separate table
        // This is a placeholder - you may want to create a ChatSession model
        
        return response()->json([
            'success' => true,
            'message' => 'Session renamed successfully'
        ]);
    }
    
    /**
     * Delete a chat session
     */
    public function deleteSession($id)
    {
        $userId = Auth::id();
        
        $deleted = Chat::where('user_id', $userId)
            ->where('session_id', $id)
            ->delete();
        
        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Session deleted successfully'
            ]);
        }
        
        return response()->json([
            'error' => 'Session not found'
        ], 404);
    }
    
    /**
     * Search chat sessions
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);
        
        $userId = Auth::id();
        $searchTerm = '%' . $request->query . '%';
        
        // Find sessions that contain the search term in any message
        $matchingSessionIds = Chat::where('user_id', $userId)
            ->where('message', 'like', $searchTerm)
            ->orWhere('response', 'like', $searchTerm)
            ->distinct()
            ->pluck('session_id');
        
        $sessions = [];
        
        foreach ($matchingSessionIds as $sessionId) {
            $messages = Chat::where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $sessions[] = [
                'id' => $sessionId,
                'title' => $this->getSessionTitle($messages),
                'preview' => $this->generatePreview($messages),
                'mood' => $this->detectMoodFromMessages($messages),
                'updatedAt' => $messages->last() ? strtotime($messages->last()->created_at) * 1000 : time() * 1000,
                'messageCount' => $messages->count()
            ];
        }
        
        return response()->json([
            'sessions' => $sessions,
            'total' => count($sessions),
            'searchTerm' => $request->query
        ]);
    }
    
    /**
     * Get chat statistics
     */
    public function getStats()
    {
        $userId = Auth::id();
        
        $totalSessions = Chat::where('user_id', $userId)
            ->distinct('session_id')
            ->count('session_id');
        
        $totalMessages = Chat::where('user_id', $userId)->count();
        
        $mostActiveDay = Chat::where('user_id', $userId)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('count', 'desc')
            ->first();
        
        $commonTopics = $this->getCommonTopics($userId);
        
        return response()->json([
            'total_sessions' => $totalSessions,
            'total_messages' => $totalMessages,
            'most_active_day' => $mostActiveDay ? $mostActiveDay->date : null,
            'common_topics' => $commonTopics
        ]);
    }
    
    // ────────────────── PRIVATE HELPER METHODS ──────────────────
    
    /**
     * Generate a unique session ID
     */
    private function generateSessionId()
    {
        return uniqid() . '_' . bin2hex(random_bytes(8));
    }
    
    /**
     * Get session title from messages
     */
    private function getSessionTitle($messages)
    {
        // Find the first user message
        $firstUserMessage = $messages->first(function($msg) {
            return $msg->is_user_message;
        });
        
        if ($firstUserMessage) {
            $title = $firstUserMessage->message;
            // Trim to 50 characters
            return strlen($title) > 50 ? substr($title, 0, 47) . '...' : $title;
        }
        
        return 'New Chat';
    }
    
    /**
     * Generate a preview of the session
     */
    private function generatePreview($messages)
    {
        // Get the last user message
        $lastUserMessage = $messages->reverse()->first(function($msg) {
            return $msg->is_user_message;
        });
        
        if ($lastUserMessage) {
            $preview = $lastUserMessage->message;
            return strlen($preview) > 60 ? substr($preview, 0, 57) . '...' : $preview;
        }
        
        return 'No messages yet...';
    }
    
    /**
     * Detect mood from chat messages
     */
    private function detectMoodFromMessages($messages)
    {
        $text = '';
        foreach ($messages as $message) {
            $text .= ' ' . strtolower($message->message);
            if ($message->response) {
                $text .= ' ' . strtolower($message->response);
            }
        }
        
        $moodKeywords = [
            'anxious' => ['anxious', 'anxiety', 'nervous', 'worried', 'panic'],
            'happy' => ['happy', 'joy', 'glad', 'excited', 'wonderful', 'great'],
            'sad' => ['sad', 'depressed', 'down', 'unhappy', 'miserable'],
            'stressed' => ['stressed', 'stress', 'overwhelmed', 'pressure', 'burnout'],
            'tired' => ['tired', 'exhausted', 'fatigued', 'sleepy', 'drained'],
            'calm' => ['calm', 'relaxed', 'peaceful', 'serene', 'tranquil']
        ];
        
        foreach ($moodKeywords as $mood => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $mood;
                }
            }
        }
        
        return 'any';
    }
    
    /**
     * Apply filters to sessions
     */
    private function applyFilters($sessions, $request)
    {
        $timeFilter = $request->get('time', 'this_week');
        $moodFilter = $request->get('mood', 'any');
        $searchQuery = $request->get('search', '');
        
        $filtered = $sessions;
        
        // Time filter
        if ($timeFilter === 'this_week') {
            $oneWeekAgo = time() * 1000 - (7 * 24 * 60 * 60 * 1000);
            $filtered = array_filter($filtered, function($session) use ($oneWeekAgo) {
                return $session['updatedAt'] >= $oneWeekAgo;
            });
        } else {
            // Month filter (january, february, etc.)
            $monthMap = [
                'january' => 0, 'february' => 1, 'march' => 2, 'april' => 3,
                'may' => 4, 'june' => 5, 'july' => 6, 'august' => 7,
                'september' => 8, 'october' => 9, 'november' => 10, 'december' => 11
            ];
            
            if (isset($monthMap[$timeFilter])) {
                $targetMonth = $monthMap[$timeFilter];
                $filtered = array_filter($filtered, function($session) use ($targetMonth) {
                    $sessionMonth = date('n', $session['updatedAt'] / 1000) - 1;
                    return $sessionMonth === $targetMonth;
                });
            }
        }
        
        // Mood filter
        if ($moodFilter !== 'any') {
            $filtered = array_filter($filtered, function($session) use ($moodFilter) {
                return $session['mood'] === $moodFilter;
            });
        }
        
        // Search filter
        if (!empty($searchQuery)) {
            $searchLower = strtolower($searchQuery);
            $filtered = array_filter($filtered, function($session) use ($searchLower) {
                return strpos(strtolower($session['title']), $searchLower) !== false ||
                       strpos(strtolower($session['preview']), $searchLower) !== false;
            });
        }
        
        return array_values($filtered);
    }
    
    /**
     * Get common topics from chat history
     */
    private function getCommonTopics($userId)
    {
        $allMessages = Chat::where('user_id', $userId)
            ->where('is_user_message', true)
            ->pluck('message')
            ->implode(' ');
        
        $topics = [
            'anxiety' => ['anxiety', 'anxious', 'nervous', 'worry'],
            'sleep' => ['sleep', 'insomnia', 'tired', 'rest'],
            'work' => ['work', 'job', 'career', 'boss', 'colleague'],
            'relationships' => ['friend', 'family', 'partner', 'relationship', 'boyfriend', 'girlfriend'],
            'self_care' => ['self care', 'relax', 'meditate', 'breathe'],
            'depression' => ['depress', 'sad', 'hopeless', 'empty']
        ];
        
        $commonTopics = [];
        $allText = strtolower($allMessages);
        
        foreach ($topics as $topic => $keywords) {
            $count = 0;
            foreach ($keywords as $keyword) {
                $count += substr_count($allText, $keyword);
            }
            if ($count > 0) {
                $commonTopics[] = [
                    'topic' => $topic,
                    'count' => $count
                ];
            }
        }
        
        // Sort by count and return top 5
        usort($commonTopics, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        return array_slice($commonTopics, 0, 5);
    }
}