<?php

namespace App\Services;

use App\Interfaces\ChatRepositoryInterface;
use App\Models\Chat;
use Exception;

class ChatService
{
    public function __construct(
        protected ChatRepositoryInterface $chatRepository,
        protected OpenAIService $openaiService
    ) {}

    public function getOrCreateChat(?int $chatId = null): Chat
    {
        if ($chatId) {
            return $this->chatRepository->getChatWithMessages($chatId);
        }

        return $this->chatRepository->createChat('New Conversation');
    }

    public function processMessage(int $chatId, string $messageContent): array
    {
        // 1. Store user message
        $this->chatRepository->storeMessage($chatId, 'user', $messageContent);

        // 2. Get history for context
        $history = $this->chatRepository->getChatHistory($chatId);
        
        $openaiMessages = $history->map(fn($msg) => [
            'role' => $msg->role,
            'content' => $msg->content
        ])->toArray();

        // 3. Get AI response
        try {
            $aiResponse = $this->openaiService->getCompletion($openaiMessages);
            
            // 4. Store AI response
            $this->chatRepository->storeMessage($chatId, 'assistant', $aiResponse);

            return [
                'role' => 'assistant',
                'content' => $aiResponse
            ];
        } catch (Exception $e) {
            return [
                'role' => 'system',
                'content' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
