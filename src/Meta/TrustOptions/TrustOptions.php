<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Meta\TrustOptions;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class TrustOptions
 *
 * @package Phayne\Saml\Meta\TrustOptions
 */
class TrustOptions
{
    public bool $encryptAssertions = true;

    public bool $encryptAuthnRequests = false;

    public bool $signResponse = true;

    public bool $signAssertions = true;

    public bool $signAuthnRequest = false;

    public string $signatureDigestAlgorithm = XMLSecurityDSig::SHA1;

    public string $blockEncryptionAlgorithm = XMLSecurityKey::AES128_CBC;

    public string $keyTransportEncryptionAlgorithm = XMLSecurityKey::RSA_OAEP_MGF1P;
}
