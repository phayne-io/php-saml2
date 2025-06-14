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
 * Class ProxyRestriction
 *
 * @package Phayne\Saml\Model\Assertion
 */
class ProxyRestriction extends AbstractCondition
{
    public function __construct(protected(set) ?int $count = null, protected(set) ?array $audience = null)
    {
    }

    public function addAudience(string $audience): ProxyRestriction
    {
        $this->audience[] = $audience;
        return $this;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('ProxyRestriction', SamlConstant::NS_ASSERTION, $parent, $context);
        $this->attributesToXml(['count'], $element);
        $this->manyElementsToXml($this->audience, $element, $context, 'Audience');
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'ProxyRestriction', SamlConstant::NS_ASSERTION);
        $this->attributesFromXml($node, ['count']);
        $this->manyElementsFromXml(
            $node,
            $context,
            'Audience',
            'saml',
            null,
            'addAudience'
        );
    }
}
