<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Provider\Credential;

use Override;
use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Credential\KeyHelper;
use Phayne\Saml\Credential\X509Certificate;
use Phayne\Saml\Credential\X509Credential;

/**
 * Class X509CredentialFileProvider
 *
 * @package Phayne\Saml\Provider\Credential
 */
class X509CredentialFileProvider implements CredentialProviderInterface
{
    private ?X509Credential $credential = null;

    public function __construct(
        private string $entityId,
        private string $certificatePath,
        private string $privateKeyPath,
        private string $privateKeyPassword
    ) {
    }

    #[Override]
    public function get(): CredentialInterface
    {
        if (null === $this->credential) {
            $this->credential = new X509Credential(
                X509Certificate::fromFile($this->certificatePath),
                KeyHelper::createPrivateKey($this->privateKeyPath, $this->privateKeyPassword, true)
            );
            $this->credential->entityId = $this->entityId;
        }

        return $this->credential;
    }
}
