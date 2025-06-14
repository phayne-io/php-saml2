<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Credential;

use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class X509Credential
 *
 * @package Phayne\Saml\Credential
 */
class X509Credential extends AbstractCredential implements X509CredentialInterface
{
    protected(set) X509Certificate $certificate {
        get {
            return $this->certificate;
        }
    }

    public function __construct(X509Certificate $certificate, ?XMLSecurityKey $privateKey = null)
    {
        parent::__construct();
        $this->publicKey = KeyHelper::createPublicKey($this->certificate);
        $this->keyNames = [$this->certificate->name()];

        if (null !== $privateKey) {
            $this->privateKey = $privateKey;
        }
    }
}
