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

use Phayne\Saml\Credential\CredentialInterface;

/**
 * Interface CredentialStoreInterface
 *
 * @package Phayne\Saml\Store\Credential
 */
interface CredentialStoreInterface
{
    /**
     * @param string $entityId
     * @return CredentialInterface[]
     */
    public function entityById(string $entityId): array;
}
