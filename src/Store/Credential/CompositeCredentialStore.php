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

use function array_merge;

/**
 * Class CompositeCredentialStore
 *
 * @package Phayne\Saml\Store\Credential
 */
class CompositeCredentialStore implements CredentialStoreInterface
{
    protected array $stores = [];

    #[Override]
    public function entityById(string $entityId): array
    {
        $result = [];

        foreach ($this->stores as $store) {
            $result = array_merge($result, $store->entityById($entityId));
        }

        return $result;
    }

    public function add(CredentialStoreInterface $store): CompositeCredentialStore
    {
        $this->stores[] = $store;
        return $this;
    }
}
