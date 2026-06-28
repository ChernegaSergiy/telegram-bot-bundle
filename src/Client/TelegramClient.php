<?php

namespace Morfeditorial\TelegramBotBundle\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * A modern replacement for tgLib.php
 * Handles all direct communication with the Telegram Bot API using Symfony HTTP Client.
 */
class TelegramClient
{
    private const API_URL = 'https://api.telegram.org/bot%s/%s';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $botToken
    ) {}

    /**
     * Sends a generic API request to Telegram
     */
    public function request(string $method, array $parameters = []): array
    {
        $url = sprintf(self::API_URL, $this->botToken, $method);

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => $parameters,
            ]);

            $content = $response->toArray();

            if (!isset($content['ok']) || $content['ok'] !== true) {
                throw new \RuntimeException('Telegram API Error: ' . ($content['description'] ?? 'Unknown error'));
            }

            return $content['result'] ?? [];
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException('Network error while communicating with Telegram: ' . $e->getMessage(), 0, $e);
        }
    }

    public function sendMessage(int|string $chatId, string $text, array $extraParams = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $extraParams);

        return $this->request('sendMessage', $params);
    }

    public function answerCallbackQuery(string $callbackQueryId, array $extraParams = []): array
    {
        $params = array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $extraParams);

        return $this->request('answerCallbackQuery', $params);
    }

    public function editMessageText(int|string $chatId, int $messageId, string $text, array $extraParams = []): array
    {
        $params = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $extraParams);

        return $this->request('editMessageText', $params);
    }
    
    public function deleteMessage(int|string $chatId, int $messageId): array
    {
        return $this->request('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function getUpdates(int $offset = 0, int $limit = 100, int $timeout = 30): array
    {
        return $this->request('getUpdates', [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => $timeout,
        ]);
    }
}
