<?php

namespace Morfeditorial\TelegramBotBundle\Command;

use Morfeditorial\TelegramBotBundle\Client\TelegramClient;

abstract class AbstractCommand implements CommandInterface
{
    public function __construct(
        protected readonly TelegramClient $client,
    ) {
    }

    public function getAliases(): array
    {
        return [];
    }

    /**
     * Automatically extracts the chat ID from the update and sends a reply message.
     */
    protected function replyWithMessage(array $update, string $text, array $extraParams = []): array
    {
        $chatId = $update['message']['chat']['id'] ?? null;

        if (!$chatId) {
            throw new \RuntimeException('Cannot reply: no chat_id found in the update.');
        }

        return $this->client->sendMessage($chatId, $text, $extraParams);
    }
}
