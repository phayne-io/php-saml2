<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Protocol;

use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class NameIDPolicy
 *
 * @package Phayne\Saml\Model\Protocol
 */
class NameIDPolicy extends AbstractSamlModel
{
    public ?string $spNameQualifier = null;

    public function __construct(
        public ?string $format = null,
        public ?bool $allowCreate = null {
            set(string|int|bool|null $value) {
                if (is_string($value) || is_int($value)) {
                    $this->allowCreate = 0 === strcasecmp($value, 'true') || 1 === $value;
                } elseif (is_bool($value) || is_null($value)) {
                    $this->allowCreate = $value;
                }
            }
        }
    ) {
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('NameIDPolicy', SamlConstant::PROTOCOL_SAML2, $parent, $context);
        $this->attributesToXml(['Format', 'SPNameQualifier', 'AllowCreate'], $element);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'NameIDPolicy', SamlConstant::PROTOCOL_SAML2);
        $this->attributesFromXml($node, ['Format', 'SPNameQualifier', 'AllowCreate']);
    }
}
