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
use Phayne\Saml\Credential\KeyHelper;
use Phayne\Saml\Credential\X509Certificate;
use Phayne\Saml\Credential\X509Credential;

/**
 * Class X509FileCredentialStore
 *
 * @package Phayne\Saml\Store\Credential
 */
class X509FileCredentialStore implements CredentialStoreInterface
{
    private ?X509Credential $credential = null;

    public function __construct(
        private readonly string $entityId,
        private readonly string $certificatePath,
        private readonly string $keyPath,
        private readonly string $password
    ) {
    }

    #[Override]
    public function entityById(string $entityId): array
    {
        if ($entityId !== $this->entityId) {
            return [];
        }

        if (null === $this->credential) {
            $certificate = X509Certificate::fromFile($this->certificatePath);
            $this->credential = new X509Credential(
                $certificate,
                KeyHelper::createPrivateKey($this->keyPath, $this->password, true, $certificate->signatureAlgorithm())
            );
        }

        return [$this->credential];
    }
}
