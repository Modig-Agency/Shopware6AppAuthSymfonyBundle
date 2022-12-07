<?php
namespace Modig\ShopwareAppAuthenticationBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class AppActivationEvent extends Event
{
    private int $shopId;

    public function __construct(int $shopId)
    {
        $this->shopId = $shopId;
    }

    public function setShopId(int $shopId): void
    {
        $this->shopId = $shopId;
    }

    public function getShopId(): int
    {
        return $this->shopId;
    }
}