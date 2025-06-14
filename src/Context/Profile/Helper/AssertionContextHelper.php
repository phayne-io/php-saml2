<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Context\Profile\Helper;

use Phayne\Saml\Context\Profile\AssertionContext;
use Phayne\Saml\Exception\SamlContextException;
use Phayne\Saml\Model\Assertion\EncryptedAssertionReader;

/**
 * Class AssertionContextHelper
 *
 * @package Phayne\Saml\Context\Profile\Helper
 */
abstract class AssertionContextHelper
{
    public static function encryptedAssertionReader(AssertionContext $context): EncryptedAssertionReader
    {
        $assertion = $context->encryptedAssertion;

        if ($assertion instanceof EncryptedAssertionReader) {
            return $assertion;
        }

        throw new SamlContextException($context, 'Expected encrypted assertion reader');
    }
}
