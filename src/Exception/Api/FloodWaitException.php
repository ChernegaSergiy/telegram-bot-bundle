<?php

namespace Morfeditorial\TelegramBotBundle\Exception\Api;

use Morfeditorial\TelegramBotBundle\Exception\TelegramApiException;

class FloodWaitException extends TelegramApiException
{
    /**
     * Gets the number of seconds to wait before repeating the request.
     */
    public function getRetryAfter(): int
    {
        $parameters = $this->getParameters();

        return $parameters['retry_after'] ?? 0;
    }
}
