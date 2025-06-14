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
 * Class AudienceRestriction
 *
 * @package Phayne\Saml\Model\Assertion
 */
class AudienceRestriction extends AbstractCondition
{
    public function __construct(protected(set) array $audience = [])
    {
    }

    public function addAudience(string $audience): AudienceRestriction
    {
        $this->audience[] = $audience;
        return $this;
    }

    public function hasAudience(string $value): bool
    {
        return in_array($value, $this->audience, true);
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement(
            'AudienceRestriction',
            SamlConstant::NS_ASSERTION,
            $parent,
            $context
        );

        $this->manyElementsToXml(
            $this->audience,
            $element,
            $context,
            'Audience',
            SamlConstant::NS_ASSERTION
        );
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AudienceRestriction', SamlConstant::NS_ASSERTION);

        $this->audience = [];
        $this->manyElementsFromXml($node, $context, 'Audience', 'saml', null, 'addAudience');
    }
}
