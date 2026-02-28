<?php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Exception;

class OpenAIService
{
    public function getCompletion(array $messages): string
    {
        if (empty(config('gemini.api_key'))) {
            return "⚠️ **Gemini API Key Missing!**\n\nI cannot respond because your GEMINI_API_KEY is not set in the `.env` file.";
        }

        try {
            // Convert OpenAI format to Gemini format
            // OpenAI: [['role' => 'user', 'content' => 'hi']]
            // Gemini: [['role' => 'user', 'parts' => [['text' => 'hi']]]]
            
            $history = [];
            $lastMessage = array_pop($messages);
            
            foreach ($messages as $msg) {
                $history[] = [
                    'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [
                        ['text' => $msg['content']]
                    ]
                ];
            }

            $chat = Gemini::chat();
            $response = $chat->sendMessage($lastMessage['content'], $history);

            return $response->text();
        } catch (Exception $e) {
            throw new Exception('Gemini API Error: ' . $e->getMessage());
        }
    }
}
