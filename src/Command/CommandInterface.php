<?php

namespace Morfeditorial\TelegramBotBundle\Command;

interface CommandInterface
{
    /**
     * Returns the command name (e.g., "start" for handling the "/start" command).
     *
     * @return string
     */
    public function getCommand(): string;

    /**
     * Returns an array of alternative command names (aliases).
     *
     * @return array
     */
    public function getAliases(): array;

    /**
     * Executes the command's logic.
     *
     * @param array $update The raw Telegram update array containing the message.
     */
    public function handle(array $update): void;
}
