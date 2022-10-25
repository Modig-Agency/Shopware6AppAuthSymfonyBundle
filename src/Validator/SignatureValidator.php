<?php

namespace Modig\ShopwareAppAuthenticationBundle\Validator;

use Modig\ShopwareAppAuthenticationBundle\Entity\ShopInterface;
use Symfony\Component\HttpFoundation\Request;

class SignatureValidator
{
    public function isValidRegistrationSignature(Request $request): bool
    {
        $appSecret = $_SERVER['SW_APP_SECRET'];
        $hmac = \hash_hmac('sha256', htmlspecialchars_decode($request->server->get('QUERY_STRING')), $appSecret);

        return hash_equals($hmac, $request->headers->get('shopware-app-signature'));
    }


    public function isValidConfirmationSignature(Request $request, ShopInterface $shop): bool
    {
        return hash_equals(
            \hash_hmac('sha256', $request->getContent(), $shop->getShopSecret()),
            $request->headers->get('shopware-shop-signature')
        );
    }

    public function isValid(Request $request, ShopInterface $shop): bool
    {
        $data = $request->query->all();

        if (array_key_exists('sw-context-language', $data)) {
            // Shopware 6.4.5 and higher
            $queryString = sprintf(
                'shop-id=%s&shop-url=%s&timestamp=%s&sw-version=%s&sw-context-language=%s&sw-user-language=%s',
                $data['shop-id'],
                $data['shop-url'],
                $data['timestamp'],
                $data['sw-version'],
                $data['sw-context-language'],
                $data['sw-user-language']
            );
        } else {
            // Shopware lower than 6.4.5
            $queryString = sprintf(
                'shop-id=%s&shop-url=%s&timestamp=%s&sw-version=%s',
                $data['shop-id'],
                $data['shop-url'],
                $data['timestamp'],
                $data['sw-version']
            );
        }

        $hmac = \hash_hmac('sha256', htmlspecialchars_decode($queryString), $shop->getShopSecret());

        return hash_equals($hmac, $data['shopware-shop-signature']);
    }

}