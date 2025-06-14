<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Model\Metadata;

use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class AssertionConsumerService
 *
 * @package Phayne\Saml\Model\Metadata
 */
class AssertionConsumerService extends IndexedEndpoint
{
    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('AssertionConsumerService', SamlConstant::NS_METADATA, $parent, $context);
        parent::serialize($element, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AssertionConsumerService', SamlConstant::NS_METADATA);
        parent::deserialize($node, $context);
    }
}
