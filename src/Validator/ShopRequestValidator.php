<?php

namespace Modig\ShopwareAppAuthenticationBundle\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Modig\ShopwareAppAuthenticationBundle\Entity\ShopInterface;
use Modig\ShopwareAppAuthenticationBundle\Repository\ShopRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class ShopRequestValidator
{
    private string $error;
    private ShopInterface $shop;

    private SignatureValidator $signatureValidator;
    private ShopRepositoryInterface $shopRepo;

    public function __construct(EntityManagerInterface $entityManager,
                                SignatureValidator $signatureValidator,
                                string $shopEntityClass)
    {
        $this->signatureValidator = $signatureValidator;
        $this->shopRepo = $entityManager->getRepository($shopEntityClass);
    }

    public function isValidAdminRequest(Request $request): bool
    {
        $data = $request->query->all();

        if (!array_key_exists('shop-id', $data)
            || !array_key_exists('shop-url', $data)
            || !array_key_exists('shopware-shop-signature', $data)
        ) {
            $this->error = 'Missing argument';
            return false;
        }

        /** @var ShopInterface $shop */
        $this->shop = $this->shopRepo->getByExternalIdAndUrl($data['shop-id'], $data['shop-url']);
        if (!$this->shop) {
            $this->error = 'Shop not found';
            return false;
        }

        if (!$this->signatureValidator->isValid($request, $this->shop)) {
            $this->error = 'invalid signature';
            return false;
        }

        return true;
    }

    public function isValidAppLifecycleEventRequest(Request $request): bool
    {
        $parameters = json_decode($request->getContent(), true);

        if (!array_key_exists('source', $parameters)
            || !array_key_exists('shopId', $parameters['source'])
            || !array_key_exists('url', $parameters['source'])
            || !array_key_exists('data', $parameters)
            || !array_key_exists('event', $parameters['data'])
        ) {
            $this->error = 'Missing argument';
            return false;
        }

        $this->shop = $this->shopRepo
            ->getByExternalIdAndUrl($parameters['source']['shopId'], stripcslashes($parameters['source']['url']));

        if (!$this->shop) {
            $this->error = 'Shop not found';
            return false;
        }

        return true;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getShop(): ShopInterface
    {
        return $this->shop;
    }
}