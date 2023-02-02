<?php declare(strict_types=1);

namespace Modig\ShopwareAppAuthenticationBundle\Controller;

use Modig\ShopwareAppAuthenticationBundle\Event\AppActivationEvent;
use Modig\ShopwareAppAuthenticationBundle\Validator\ShopRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Modig\ShopwareAppAuthenticationBundle\Entity\ShopInterface;
use Modig\ShopwareAppAuthenticationBundle\Repository\ShopRepositoryInterface;
use Modig\ShopwareAppAuthenticationBundle\Validator\SignatureValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AppAuthenticationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SignatureValidator $signatureValidator;
    private ShopRequestValidator $shopRequestValidator;
    private RouterInterface $router;
    private string $shopEntityClass;

    public function __construct(EntityManagerInterface $entityManager,
                                SignatureValidator $signatureValidator,
                                RouterInterface $router,
                                ShopRequestValidator $shopRequestValidator,
                                string $shopEntityClass)
    {
        $this->shopEntityClass = $shopEntityClass;
        $this->entityManager = $entityManager;
        $this->signatureValidator = $signatureValidator;
        $this->shopRequestValidator = $shopRequestValidator;
        $this->router = $router;
    }

    public function registration(Request $request): Response
    {
        if (!$this->signatureValidator->isValidRegistrationSignature($request)) {

            return new JsonResponse([
                'error' => 'Registration failed! Invalid signature.',
            ]);
        }

        $appName = $_SERVER['SW_APP_NAME'];
        $appSecret = $_SERVER['SW_APP_SECRET'];
        $proof = \hash_hmac(
            'sha256',
            $request->get('shop-id') . $request->get('shop-url') . $appName,
            $appSecret);

        /** @var ShopRepositoryInterface $shopRepo */
        $shopRepo = $this->entityManager->getRepository($this->shopEntityClass);
        /** @var ShopInterface $shop */
        $shop = $shopRepo->getByExternalId($request->get('shop-id'));
        if (empty($shop)) {
            $shop = new $this->shopEntityClass();
        }

        $shop->setExternalId($request->get('shop-id'));
        $shop->setUrl($request->get('shop-url'));
        $shop->setShopSecret(Uuid::v4()->toRfc4122());
        $shop->setAppSecret($appSecret);
        $shop->setStatus(ShopInterface::STATUS_INSTALLED);

        $this->entityManager->persist($shop);
        $this->entityManager->flush();

        return new JsonResponse([
            'proof' => $proof,
            'secret' => $shop->getShopSecret(),
            'confirmation_url' => $this->getParameter('app.url') . $this->router->generate('modig.shopware_app_authentication_bundle.app_auth_controller.confirm', [], UrlGeneratorInterface::ABSOLUTE_PATH),
        ]);
    }

    public function confirm(Request $request): Response
    {
        $data = $request->toArray();

        /** @var ShopRepositoryInterface $shopRepo */
        $shopRepo = $this->entityManager->getRepository($this->shopEntityClass);
        $shop = $shopRepo->getByExternalId($data['shopId']);
        if (!$shop) {
            return new Response(null, 401);
        }

        if (!$this->signatureValidator->isValidConfirmationSignature($request, $shop)) {
            return new Response(null, 401);
        }

        $shop->setApiKey($data['apiKey']);
        $shop->setSecretKey($data['secretKey']);

        $this->entityManager->persist($shop);
        $this->entityManager->flush();

        return new Response();
    }

    public function activate(Request $request,
                             EventDispatcherInterface $eventDispatcher): Response
    {
        if (!$this->shopRequestValidator->isValidAppLifecycleEventRequest($request)) {
            return new Response($this->shopRequestValidator->getError(), 400);
        }

        $shop = $this->shopRequestValidator->getShop();
        $shop->setStatus(ShopInterface::STATUS_ACTIVE);
        $this->entityManager->persist($shop);
        $this->entityManager->flush();

        $event = new AppActivationEvent($shop->getId());
        $eventDispatcher->dispatch($event);

        return new Response();
    }

    public function deactivate(Request $request): Response
    {
        if (!$this->shopRequestValidator->isValidAppLifecycleEventRequest($request)) {
            return new Response($this->shopRequestValidator->getError(), 400);
        }

        $shop = $this->shopRequestValidator->getShop();
        $shop->setStatus(ShopInterface::STATUS_INACTIVE);
        $this->entityManager->persist($shop);
        $this->entityManager->flush();

        return new Response();
    }

    public function delete(Request $request): Response
    {
        if (!$this->shopRequestValidator->isValidAppLifecycleEventRequest($request)) {
            return new Response($this->shopRequestValidator->getError(), 400);
        }

        $shop = $this->shopRequestValidator->getShop();
        $shop->setStatus(ShopInterface::STATUS_DELETED);
        $this->entityManager->persist($shop);
        $this->entityManager->flush();

        return new Response();
    }
}
