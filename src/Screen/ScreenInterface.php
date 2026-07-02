<?php

namespace Morfeditorial\TelegramBotBundle\Screen;

interface ScreenInterface
{
    /**
     * Determines if this screen can handle the given update.
     *
     * @param array $update the raw Telegram update array
     *
     * @return bool true if this screen should handle the update
     */
    public function supports(array $update): bool;

    /**
     * Handles the update.
     *
     * @param array $update the raw Telegram update array
     */
    public function handle(array $update): void;
}
