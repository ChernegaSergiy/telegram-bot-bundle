<?php

namespace Morfeditorial\TelegramBotBundle\Exception;

class TelegramApiException extends \RuntimeException
{
    private int $telegramErrorCode;
    private string $telegramDescription;
    private array $parameters;

    public function __construct(string $description, int $errorCode = 0, array $parameters = [], \Throwable $previous = null)
    {
        $this->telegramErrorCode = $errorCode;
        $this->telegramDescription = $description;
        $this->parameters = $parameters;
        
        $message = sprintf('Telegram API Error %d: %s', $errorCode, $description);
        parent::__construct($message, $errorCode, $previous);
    }

    public function getTelegramErrorCode(): int
    {
        return $this->telegramErrorCode;
    }

    public function getTelegramDescription(): string
    {
        return $this->telegramDescription;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Telegram does not provide a specific error code for "message is not modified"
     * (it just returns 400 Bad Request). We must check the description string.
     */
    public function isMessageNotModified(): bool
    {
        return $this->telegramErrorCode === 400 && str_contains(strtolower($this->telegramDescription), 'message is not modified');
    }
}
