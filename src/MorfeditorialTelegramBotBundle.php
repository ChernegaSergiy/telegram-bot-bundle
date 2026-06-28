<?php

namespace Morfeditorial\TelegramBotBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MorfeditorialTelegramBotBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
