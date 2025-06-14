<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Assertion;

use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class EncryptedAssertionWriter
 *
 * @package Phayne\Saml\Model\Assertion
 */
class EncryptedAssertionWriter extends EncryptedElementWriter
{
    #[Override]
    protected function createRootElement(DOMNode $parent, Context\SerializationContext $context): DOMElement
    {
        return $this->createElement('saml:EncryptedAssertion', SamlConstant::NS_ASSERTION, $parent, $context);
    }
}
