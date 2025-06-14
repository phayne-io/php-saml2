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

use BackedEnum;
use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

use function is_array;

/**
 * Class Attribute
 *
 * @package Phayne\Saml\Model\Assertion
 */
class Attribute extends AbstractSamlModel
{
    protected(set) ?string $nameFormat = null;

    protected(set) ?string $friendlyName = null;

    protected(set) array $attributeValue {
        set(array | string $value) {
            $this->attributeValue = is_array($value) ? $value : [$value];
        }
    }

    public function __construct(protected(set) string|BackedEnum|null $name = null, string|array|null $value = null)
    {
        if ($this->name instanceof BackedEnum) {
            $this->name = $this->name->value;
        }

        if (null !== $value) {
            $this->attributeValue = is_array($value) ? $value : [$value];
        }
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('Attribute', SamlConstant::NS_ASSERTION, $parent, $context);
        $this->attributesToXml(['Name', 'NameFormat', 'FriendlyName'], $result);
        $this->manyElementsToXml($this->attributeValue, $result, $context, 'AttributeValue', SamlConstant::NS_ASSERTION);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Attribute', SamlConstant::NS_ASSERTION);
        $this->attributesFromXml($node, ['Name', 'NameFormat', 'FriendlyName']);
        $this->attributeValue = [];
        $this->manyElementsFromXml($node, $context, 'AttributeValue', 'saml', null, 'addAttributeValue');
    }
}
