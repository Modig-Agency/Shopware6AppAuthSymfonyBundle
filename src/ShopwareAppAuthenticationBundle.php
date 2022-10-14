<?php

namespace Modig\ShopwareAppAuthenticationBundle;

use Modig\ShopwareAppAuthenticationBundle\DependencyInjection\ShopwareAppAuthenticationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShopwareAppAuthenticationBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new ShopwareAppAuthenticationExtension();
        }

        return $this->extension;
    }
}
