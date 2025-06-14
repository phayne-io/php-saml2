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

use Phayne\Saml\Credential\Context\CredentialContextSet;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class AbstractCredential
 *
 * @package Phayne\Saml\Credential
 */
class AbstractCredential implements CredentialInterface
{
    protected(set) string $entityId {
        get {
            return $this->entityId;
        }
    }

    protected(set) string $usageType {
        get {
            return $this->usageType;
        }
    }

    protected(set) CredentialContextSet $credentialContext {
        get {
            return $this->credentialContext;
        }
    }

    protected(set) array $keyNames {
        get {
            return $this->keyNames;
        }
    }

    protected(set) ?XmlSecurityKey $publicKey {
        get {
            return $this->publicKey;
        }
    }

    protected(set) ?XmlSecurityKey $privateKey {
        get {
            return $this->privateKey;
        }
    }

    protected(set) ?string $secretKey {
        get {
            return $this->secretKey;
        }
    }

    public function __construct()
    {
        $this->credentialContext = new CredentialContextSet();
    }

    public function addKeyName(string $keyName): AbstractCredential
    {
        $keyName = trim($keyName);

        if ($keyName !== '' && $keyName !== '0') {
            $this->keyNames[] = $keyName;
        }

        return $this;
    }
}
