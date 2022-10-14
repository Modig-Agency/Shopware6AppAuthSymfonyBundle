<?php

namespace Modig\ShopwareAppAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('modig_shopware_app_authentication');

        $treeBuilder->getRootNode()
            ->children()
                ->variableNode('shop_entity')
            ->end()
            ->end()
            ;

        return $treeBuilder;
    }

}