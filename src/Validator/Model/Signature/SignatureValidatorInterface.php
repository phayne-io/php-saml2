<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Validator\Model\Signature;

use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Model\XmlDSig\AbstractSignatureReader;

/**
 * Interface SignatureValidatorInterface
 *
 * @package Phayne\Saml\Validator\Model\Signature
 */
interface SignatureValidatorInterface
{
    public function validate(
        AbstractSignatureReader $signature,
        string $issuer,
        string $metadataType
    ): ?CredentialInterface;
}
