<?php

namespace Morfeditorial\TelegramBotBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TelegramWebAppAuthenticatorFactory implements AuthenticatorFactoryInterface
{
    public function getPriority(): int
    {
        return -10;
    }

    public function getKey(): string
    {
        return 'telegram_tma';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
        // No custom configuration needed yet (token is pulled from the bundle's global config)
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string|array
    {
        $authenticatorId = 'security.authenticator.telegram_tma.'.$firewallName;

        $container
            ->setDefinition($authenticatorId, new ChildDefinition('morfeditorial.telegram_bot.security.authenticator'))
            ->replaceArgument(0, '%telegram_bot.token%')
            ->replaceArgument(1, new Reference('event_dispatcher'));

        return $authenticatorId;
    }
}
