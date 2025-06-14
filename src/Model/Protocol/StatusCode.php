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
 * Class StatusCode
 *
 * @package Phayne\Saml\Model\Protocol
 */
class StatusCode extends AbstractSamlModel
{
    public ?StatusCode $statusCode = null;

    public function __construct(public string|SamlConstant|null $value = null)
    {
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('samlp:StatusCode', SamlConstant::PROTOCOL_SAML2, $parent, $context);
        $this->attributesToXml(['Value'], $element);
        $this->singleElementsToXml(['StatusCode'], $element, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'StatusCode', SamlConstant::PROTOCOL_SAML2);
        $this->attributesFromXml($node, ['Value']);
        $this->singleElementsFromXml($node, $context, [
            'StatusCode' => ['samlp', StatusCode::class],
        ]);
    }
}
