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
use Phayne\Saml\Model\Context\DeserializationContext;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Class EncryptedAssertionReader
 *
 * @package Phayne\Saml\Model\Assertion
 */
class EncryptedAssertionReader extends EncryptedElementReader
{
    public function decryptMultiAssertion(array $inputKeys, DeserializationContext $deserializationContext): Assertion
    {
        return $this->assertionFromDom($this->decryptMulti($inputKeys), $deserializationContext);
    }

    public function decryptAssertion(
        XMLSecurityKey $credential,
        DeserializationContext $deserializationContext
    ): Assertion {
        return $this->assertionFromDom($this->decrypt($credential), $deserializationContext);
    }

    public function assertionFromDom(DOMElement $dom, DeserializationContext $deserializationContext): Assertion
    {
        $deserializationContext->document = $dom->ownerDocument;

        $assertion = new Assertion();
        $assertion->deserialize($dom, $deserializationContext);

        return $assertion;
    }
}
