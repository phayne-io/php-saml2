<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Resolver\Signature;

use Phayne\Saml\Context\Profile\AbstractProfileContext;
use Phayne\Saml\Model\XmlDSig\SignatureWriter;

/**
 * Interface SignatureResolverInterface
 *
 * @package Phayne\Saml\Resolver\Signature
 */
interface SignatureResolverInterface
{
    public function signature(AbstractProfileContext $context): ?SignatureWriter;
}
