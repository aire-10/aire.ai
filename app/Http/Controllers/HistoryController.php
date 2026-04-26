<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        return view('history');
    }
    
    public function getSessions(Request $request)
    {
        try {
            $userId = Auth::id();
            
            $chats = Chat::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('session_id');
            
            $sessions = [];
            
            foreach ($chats as $sessionId => $messages) {
                $firstMessage = $messages->first();
                $lastMessage = $messages->last();
                
                $mood = $this->detectMoodFromMessages($messages);
                $preview = $this->generatePreview($messages);
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
            
            usort($sessions, function($a, $b) {
                return $b['updatedAt'] - $a['updatedAt'];
            });
            
            return response()->json([
                'sessions' => $sessions,
                'total' => count($sessions)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('HistoryController error: ' . $e->getMessage());
            return response()->json([
                'sessions' => [],
                'total' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function getSession($id)
    {
        try {
            $userId = Auth::id();
            
            $messages = Chat::where('user_id', $userId)
                ->where('session_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();
            
            if ($messages->isEmpty()) {
                return response()->json([
                    'id' => $id,
                    'messages' => [],
                    'title' => 'New Chat'
                ]);
            }
            
            $formattedMessages = [];
            foreach ($messages as $message) {
                $formattedMessages[] = [
                    'id' => $message->id,
                    'text' => $message->is_user_message ? $message->message : ($message->response ?? $message->message),
                    'isUser' => (bool)$message->is_user_message,
                    'response' => $message->response,
                    'timestamp' => strtotime($message->created_at) * 1000
                ];
            }
            
            return response()->json([
                'id' => $id,
                'messages' => $formattedMessages,
                'title' => $this->getSessionTitle($messages)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get session error: ' . $e->getMessage());
            return response()->json([
                'id' => $id,
                'messages' => [],
                'title' => 'New Chat',
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function createSession(Request $request)
    {
        try {
            $sessionId = $this->generateSessionId();
            
            return response()->json([
                'id' => $sessionId,
                'title' => 'New Chat',
                'createdAt' => time() * 1000
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'id' => uniqid() . '_' . bin2hex(random_bytes(8)),
                'title' => 'New Chat',
                'createdAt' => time() * 1000
            ]);
        }
    }
    
    public function deleteSession($id)
    {
        try {
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
                'success' => false,
                'error' => 'Session not found'
            ], 404);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function renameSession(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Session renamed successfully'
        ]);
    }
    
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);
        
        $userId = Auth::id();
        $searchTerm = '%' . $request->query . '%';
        
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
        
        return response()->json([
            'total_sessions' => $totalSessions,
            'total_messages' => $totalMessages,
            'most_active_day' => $mostActiveDay ? $mostActiveDay->date : null
        ]);
    }
    
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
            'is_user_message' => $request->is_user
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $chat
        ]);
    }
    
    // ────────────────── PRIVATE HELPER METHODS ──────────────────
    
    private function generateSessionId()
    {
        return uniqid() . '_' . bin2hex(random_bytes(8));
    }
    
    private function getSessionTitle($messages)
    {
        $firstUserMessage = $messages->first(function($msg) {
            return $msg->is_user_message;
        });
        
        if ($firstUserMessage) {
            $title = $firstUserMessage->message;
            return strlen($title) > 50 ? substr($title, 0, 47) . '...' : $title;
        }
        
        return 'New Chat';
    }
    
    private function generatePreview($messages)
    {
        $lastUserMessage = $messages->reverse()->first(function($msg) {
            return $msg->is_user_message;
        });
        
        if ($lastUserMessage) {
            $preview = $lastUserMessage->message;
            return strlen($preview) > 60 ? substr($preview, 0, 57) . '...' : $preview;
        }
        
        return 'No messages yet...';
    }
    
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
}