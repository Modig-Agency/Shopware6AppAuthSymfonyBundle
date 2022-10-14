<?php

namespace Modig\ShopwareAppAuthenticationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ShopwareAppAuthenticationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $options = $this->processConfiguration($configuration, $configs);

        $repo = $container->getDefinition('modig.shopware_app_authentication_bundle.app_auth_controller');
        $repo->replaceArgument('$shopEntityClass', $options['shop_entity']);

        $shopReqValidator = $container->getDefinition('modig.shopware_app_authentication_bundle.validator.shop_request_validator');
        $shopReqValidator->replaceArgument('$shopEntityClass', $options['shop_entity']);
    }

    public function getAlias():string
    {
        return 'modig_shopware_app_authentication';
    }

}