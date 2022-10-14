<?php
namespace Modig\ShopwareAppAuthenticationBundle\Repository;

use Modig\ShopwareAppAuthenticationBundle\Entity\ShopInterface;

interface ShopRepositoryInterface
{
    public function getByExternalId(string $externalId): ?ShopInterface;
    public function getByExternalIdAndUrl(string $externalId, string $url): ?ShopInterface;
}