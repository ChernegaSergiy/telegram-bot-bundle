<?php

namespace TelegramBot\Bundle\Screen;

interface ScreenInterface
{
    /**
     * Determines if this screen can handle the given update.
     *
     * @param array $update The raw Telegram update array.
     * @return bool True if this screen should handle the update.
     */
    public function supports(array $update): bool;

    /**
     * Handles the update.
     *
     * @param array $update The raw Telegram update array.
     */
    public function handle(array $update): void;
}
