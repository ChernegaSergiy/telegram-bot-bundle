<?php

namespace Morfeditorial\TelegramBotBundle;

use Morfeditorial\TelegramBotBundle\DependencyInjection\Security\Factory\TelegramWebAppAuthenticatorFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class TelegramBotBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        if ($container->hasExtension('security')) {
            $extension = $container->getExtension('security');
            $extension->addAuthenticatorFactory(new TelegramWebAppAuthenticatorFactory());
        }
    }
}
