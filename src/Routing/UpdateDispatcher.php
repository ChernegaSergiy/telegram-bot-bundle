<?php

namespace morfeditorial\TelegramBotBundle\Routing;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use morfeditorial\TelegramBotBundle\Command\CommandInterface;
use morfeditorial\TelegramBotBundle\Screen\ScreenInterface;

class UpdateDispatcher
{
    /**
     * @param iterable<CommandInterface> $commands
     * @param iterable<ScreenInterface> $screens
     */
    public function __construct(
        #[TaggedIterator('telegram_bot.command')]
        private readonly iterable $commands = [],
        
        #[TaggedIterator('telegram_bot.screen')]
        private readonly iterable $screens = []
    ) {}

    /**
     * Parses the raw Telegram update and dispatches it to the appropriate Command or Screen.
     */
    public function dispatch(array $update): void
    {
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update);
            return;
        }

        if (isset($update['message']['text'])) {
            $text = $update['message']['text'];
            
            // Check if it's a slash command
            if (str_starts_with($text, '/')) {
                $this->handleCommand($update);
                return;
            }

            // If it's a regular text message, pass it to screens (they might be waiting for user input state)
            foreach ($this->screens as $screen) {
                if ($screen->supports($update)) {
                    $screen->handle($update);
                    return; // Stop routing once a screen handles the text input
                }
            }
        }

        // Additional update types (plain text messages, photos, etc.) can be handled here in the future
    }

    private function handleCallbackQuery(array $update): void
    {
        foreach ($this->screens as $screen) {
            if ($screen->supports($update)) {
                $screen->handle($update);
                return; // Stop routing once a screen handles the action
            }
        }
    }

    private function handleCommand(array $update): void
    {
        $text = $update['message']['text'] ?? '';
        
        // Extract command name (e.g., "/start" -> "start")
        $parts = explode(' ', ltrim($text, '/'), 2);
        $commandName = strtolower($parts[0]);
        
        // Handle bot mentions in groups (e.g., "/start@MyCoolBot" -> "start")
        if (str_contains($commandName, '@')) {
            $commandName = explode('@', $commandName)[0];
        }

        foreach ($this->commands as $command) {
            if ($command->getCommand() === $commandName) {
                $command->handle($update);
                return;
            }
        }
    }
}
