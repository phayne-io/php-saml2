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
 * Interface CredentialInterface
 *
 * @package Phayne\Saml\Credential
 */
interface CredentialInterface
{
    public string $entityId {
        get;
    }

    public ?string $usageType {
        get;
    }

    public array $keyNames {
        get;
    }

    public ?XMLSecurityKey $publicKey {
        get;
    }

    public ?XMLSecurityKey $privateKey {
        get;
    }

    public ?string $secretKey {
        get;
    }

    public CredentialContextSet $credentialContext {
        get;
    }
}
