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
use Phayne\Saml\Exception\SamlModelException;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class AbstractNameID
 *
 * @package Phayne\Saml\Model\Assertion
 */
abstract class AbstractNameID extends AbstractSamlModel
{
    protected(set) ?string $nameQualifier = null;

    protected(set) ?string $spNameQualifier = null;

    protected(set) ?string $spProviderId = null;

    public function __construct(protected(set) ?string $value = null, public ?string $format = null)
    {
    }

    protected function prepareForXml(): void
    {
        if (null === $this->value) {
            throw new SamlModelException('NameID value not set.');
        }
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $this->prepareForXml();
        $element = SamlConstant::is($parent->namespaceURI, SamlConstant::NS_ASSERTION)
            ? $this->createElement($this->elementName(), SamlConstant::NS_ASSERTION, $parent, $context)
            : $this->createElement(
                'saml:' . $this->elementName(),
                SamlConstant::NS_ASSERTION,
                $parent,
                $context
            );
        $this->attributesToXml(['Format', 'NameQualifier', 'SPNameQualifier', 'SPProviderID'], $element);
        $element->nodeValue = $this->value;
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, $this->elementName(), SamlConstant::NS_ASSERTION);
        $this->attributesFromXml($node, ['NameQualifier', 'Format', 'SPNameQualifier', 'SPProviderId']);

        $this->value = $node->textContent;
    }

    abstract protected function elementName(): string;
}
