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

use DateTime;
use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class AuthnStatement
 *
 * @package Phayne\Saml\Model\Assertion
 */
class AuthnStatement extends AbstractStatement
{
    protected(set) ?int $authnInstant = null;

    protected(set) ?int $sessionNotOnOrAfter = null {
        set(int | string | DateTime | null $value) {
            $this->sessionNotOnOrAfter = Helper::getTimestampFromValue($value);
        }
    }

    protected(set) ?string $sessionIndex = null;

    protected(set) ?AuthnContext $authnContext = null;

    protected(set) ?SubjectLocality $subjectLocality = null;

    public function sessionNotOnOrAfterString(): ?string
    {
        if (null !== $this->sessionNotOnOrAfter) {
            return Helper::time2string($this->sessionNotOnOrAfter);
        }

        return null;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('AuthnStatement', SamlConstant::NS_ASSERTION, $parent, $context);
        $this->attributesToXml(
            ['AuthnInstant', 'SessionNotOnOrAfter', 'SessionIndex'],
            $result
        );
        $this->singleElementsToXml(
            ['SubjectLocality', 'AuthnContext'],
            $result,
            $context
        );
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AuthnStatement', SamlConstant::NS_ASSERTION);
        $this->attributesFromXml($node, ['AuthnInstant', 'SessionNotOnOrAfter', 'SessionIndex']);
        $this->singleElementsFromXml($node, $context, [
            'SubjectLocality' => ['saml', SubjectLocality::class],
            'AuthnContext' => ['saml', AuthnContext::class],
        ]);
    }
}
