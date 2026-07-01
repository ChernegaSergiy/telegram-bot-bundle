<?php

namespace Morfeditorial\TelegramBotBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Morfeditorial\TelegramBotBundle\Command\CommandInterface;
use Morfeditorial\TelegramBotBundle\Screen\ScreenInterface;

class TelegramBotExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Store the token as a container parameter so services can use it
        $container->setParameter('telegram_bot.token', $config['token']);

        // Register interfaces for autoconfiguration so host apps don't need manual tagging
        $container->registerForAutoconfiguration(ScreenInterface::class)
            ->addTag('telegram_bot.screen');

        $container->registerForAutoconfiguration(CommandInterface::class)
            ->addTag('telegram_bot.command');

        // Load the bundle's native services (like TelegramClient and UpdateDispatcher)
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }
}
