<?php

namespace TelegramBot\Bundle\Screen;

use TelegramBot\Bundle\Client\TelegramClient;

abstract class AbstractScreen implements ScreenInterface
{
    public function __construct(
        protected readonly TelegramClient $client
    ) {}

    /**
     * Helper to quickly send a message to the chat.
     */
    protected function sendMessage(int|string $chatId, string $text, array $extraParams = []): array
    {
        return $this->client->sendMessage($chatId, $text, $extraParams);
    }

    /**
     * Helper to edit an existing message.
     */
    protected function editMessageText(int|string $chatId, int $messageId, string $text, array $extraParams = []): array
    {
        return $this->client->editMessageText($chatId, $messageId, $text, $extraParams);
    }

    /**
     * Helper to answer a callback query (removes the "loading" state from the inline button).
     */
    protected function answerCallbackQuery(string $callbackQueryId, array $extraParams = []): array
    {
        return $this->client->answerCallbackQuery($callbackQueryId, $extraParams);
    }
}
