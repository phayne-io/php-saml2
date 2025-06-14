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
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class SubjectLocality
 *
 * @package Phayne\Saml\Model\Assertion
 */
class SubjectLocality extends AbstractSamlModel
{
    protected(set) string $address;

    protected(set) string $dnsName;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('SubjectLocality', SamlConstant::NS_ASSERTION, $parent, $context);
        $this->attributesToXml(['Address', 'DNSName'], $result);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SubjectLocality', SamlConstant::NS_ASSERTION);
        $this->attributesFromXml($node, ['Address', 'DNSName']);
    }
}
