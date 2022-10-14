<?php

namespace Modig\ShopwareAppAuthenticationBundle\Entity;

interface ShopInterface
{
    const STATUS_INSTALLED = 'installed';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DELETED = 'deleted';

    public function getId(): ?int;

    public function getExternalId(): ?string;

    public function setExternalId(string $external_id): self;

    public function getUrl(): ?string;

    public function setUrl(string $url): self;

    public function getApiKey(): ?string;

    public function setApiKey(string $api_key): self;

    public function getSecretKey(): ?string;

    public function setSecretKey(string $secret_key): self;

    public function getAppSecret(): ?string;

    public function setAppSecret(string $appSecret): self;

    public function getStatus(): ?string;

    public function setStatus(string $status): self;
}