<?php

namespace TelegramBot\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('telegram_bot');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('token')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('The Telegram Bot API token.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
