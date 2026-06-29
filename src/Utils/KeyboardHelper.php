<?php

namespace morfeditorial\TelegramBotBundle\Utils;

class KeyboardHelper
{
    /**
     * Creates an inline keyboard markup JSON string.
     *
     * @param array $rows Array of rows, where each row is an array of buttons.
     */
    public static function inlineKeyboard(array $rows): string
    {
        return json_encode(['inline_keyboard' => $rows]);
    }

    /**
     * Creates a standard callback inline button array.
     */
    public static function inlineButton(string $text, string $callbackData): array
    {
        return ['text' => $text, 'callback_data' => $callbackData];
    }

    /**
     * Creates an inline button with a URL.
     */
    public static function urlButton(string $text, string $url): array
    {
        return ['text' => $text, 'url' => $url];
    }

    /**
     * Creates a reply keyboard markup JSON string.
     *
     * @param array $rows Array of rows, where each row is an array of buttons.
     * @param bool $resize Requests clients to resize the keyboard vertically for optimal fit.
     * @param bool $oneTime Requests clients to hide the keyboard as soon as it's been used.
     */
    public static function replyKeyboard(array $rows, bool $resize = true, bool $oneTime = false): string
    {
        return json_encode([
            'keyboard' => $rows,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => $oneTime,
        ]);
    }

    /**
     * Creates a standard reply button array.
     */
    public static function replyButton(string $text): array
    {
        return ['text' => $text];
    }

    /**
     * Removes the reply keyboard.
     */
    public static function removeKeyboard(): string
    {
        return json_encode(['remove_keyboard' => true]);
    }
}
