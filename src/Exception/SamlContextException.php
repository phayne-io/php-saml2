<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Exception;

use Phayne\Saml\Context\ContextInterface;
use Throwable;

/**
 * Class SamlContextException
 *
 * @package Phayne\Saml\Exception
 */
class SamlContextException extends SamlException
{
    public function __construct(
        public readonly ContextInterface $context,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
