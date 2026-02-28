<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    public function index()
    {
        $chat = $this->chatService->getOrCreateChat(session('chat_id'));
        session(['chat_id' => $chat->id]);

        return view('chat', compact('chat'));
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $chatId = session('chat_id');
        
        if (!$chatId) {
            return response()->json(['error' => 'No active chat session'], 400);
        }

        $response = $this->chatService->processMessage($chatId, $request->message);

        return response()->json($response);
    }
}
