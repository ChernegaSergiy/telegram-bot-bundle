<?php

namespace Morfeditorial\TelegramBotBundle\Event;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class TelegramUserAuthenticatedEvent extends Event
{
    private array $telegramUserData;
    private ?UserInterface $user = null;

    public function __construct(array $telegramUserData)
    {
        $this->telegramUserData = $telegramUserData;
    }

    public function getTelegramUserData(): array
    {
        return $this->telegramUserData;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }
}
