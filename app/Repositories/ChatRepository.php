<?php

namespace App\Repositories;

use App\Interfaces\ChatRepositoryInterface;
use App\Models\Chat;
use App\Models\Message;

class ChatRepository implements ChatRepositoryInterface
{
    public function createChat(?string $title = null): Chat
    {
        return Chat::create(['title' => $title]);
    }

    public function getChatWithMessages(int $chatId): Chat
    {
        return Chat::with('messages')->findOrFail($chatId);
    }

    public function storeMessage(int $chatId, string $role, string $content): Message
    {
        return Message::create([
            'chat_id' => $chatId,
            'role' => $role,
            'content' => $content,
        ]);
    }

    public function getChatHistory(int $chatId)
    {
        return Message::where('chat_id', $chatId)->orderBy('created_at', 'asc')->get();
    }
}
