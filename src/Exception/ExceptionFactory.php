<?php

namespace Morfeditorial\TelegramBotBundle\Exception;

use Morfeditorial\TelegramBotBundle\Exception\Api\BadRequestException;
use Morfeditorial\TelegramBotBundle\Exception\Api\FloodWaitException;
use Morfeditorial\TelegramBotBundle\Exception\Api\ForbiddenException;
use Morfeditorial\TelegramBotBundle\Exception\Api\InternalServerException;
use Morfeditorial\TelegramBotBundle\Exception\Api\NotAcceptableException;
use Morfeditorial\TelegramBotBundle\Exception\Api\NotFoundException;
use Morfeditorial\TelegramBotBundle\Exception\Api\SeeOtherException;
use Morfeditorial\TelegramBotBundle\Exception\Api\TimeoutException;
use Morfeditorial\TelegramBotBundle\Exception\Api\UnauthorizedException;

class ExceptionFactory
{
    /**
     * Creates the appropriate TelegramApiException based on the API response.
     */
    public static function createFromResult(array $content): TelegramApiException
    {
        $errorCode = $content['error_code'] ?? 0;
        $description = $content['description'] ?? 'Unknown error';
        $parameters = $content['parameters'] ?? [];

        return match ((int) $errorCode) {
            -503 => new TimeoutException($description, $errorCode, $parameters),
            303 => new SeeOtherException($description, $errorCode, $parameters),
            400 => new BadRequestException($description, $errorCode, $parameters),
            401 => new UnauthorizedException($description, $errorCode, $parameters),
            403 => new ForbiddenException($description, $errorCode, $parameters),
            404 => new NotFoundException($description, $errorCode, $parameters),
            406 => new NotAcceptableException($description, $errorCode, $parameters),
            420, 429 => new FloodWaitException($description, $errorCode, $parameters),
            500 => new InternalServerException($description, $errorCode, $parameters),
            default => new TelegramApiException($description, $errorCode, $parameters),
        };
    }
}
