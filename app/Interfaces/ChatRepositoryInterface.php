<?php

namespace App\Interfaces;

use App\Models\Chat;
use App\Models\Message;

interface ChatRepositoryInterface
{
    public function createChat(?string $title = null): Chat;
    public function getChatWithMessages(int $chatId): Chat;
    public function storeMessage(int $chatId, string $role, string $content): Message;
    public function getChatHistory(int $chatId);
}
