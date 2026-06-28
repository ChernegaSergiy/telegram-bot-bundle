<?php

namespace TelegramBot\Bundle\Screen;

interface ScreenInterface
{
    /**
     * Determines whether this screen supports the given callback action.
     *
     * @param string $action The action identifier from the callback data (e.g., 'author:view').
     * @return bool True if this screen should handle the action.
     */
    public function supports(string $action): bool;

    /**
     * Executes the screen's logic for the given action and update.
     *
     * @param string $action The action identifier.
     * @param array $update The raw Telegram update array.
     */
    public function handle(string $action, array $update): void;
}
