<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\Credential;

use Override;
use Phayne\Saml\Credential\CredentialInterface;

use function array_key_exists;

/**
 * Class StaticCredentialStore
 *
 * @package Phayne\Saml\Store\Credential
 */
class StaticCredentialStore implements CredentialStoreInterface
{
    protected array $credentials = [];

    #[Override]
    public function entityById(string $entityId): array
    {
        $this->checkEntityIdExistence($entityId);
        return $this->credentials[$entityId];
    }

    public function has(string $entityId): bool
    {
        return array_key_exists($entityId, $this->credentials);
    }

    public function add(CredentialInterface $credential): StaticCredentialStore
    {
        $this->checkEntityIdExistence($credential->entityId);
        $this->credentials[$credential->entityId][] = $credential;

        return $this;
    }

    private function checkEntityIdExistence(string $entityId): void
    {
        if (false === $this->has($entityId)) {
            $this->credentials[$entityId] = [];
        }
    }
}
