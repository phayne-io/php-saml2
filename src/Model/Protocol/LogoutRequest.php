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
use Phayne\Saml\Model\Assertion\NameID;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class LogoutRequest
 *
 * @package Phayne\Saml\Model\Protocol
 */
class LogoutRequest extends AbstractRequest
{
    public ?string $reason = null;

    public ?int $notOnOrAfter = null;

    public ?string $nameID = null;

    public ?string $sessionIndex = null;

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('LogoutRequest', SamlConstant::PROTOCOL_SAML2, $parent, $context);
        parent::serialize($element, $context);
        $this->attributesToXml(['Reason', 'NotOnOrAfter'], $element);
        $this->singleElementsToXml(['NameID', 'SessionIndex'], $element, $context, SamlConstant::PROTOCOL_SAML2);
        $this->singleElementsToXml(['Signature'], $element, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'LogoutRequest', SamlConstant::PROTOCOL_SAML2);
        parent::deserialize($node, $context);
        $this->attributesFromXml($node, ['Reason', 'NotOnOrAfter']);
        $this->singleElementsFromXml($node, $context, [
            'NameID' => ['saml', NameID::class],
            'SessionIndex' => ['samlp', null]
        ]);
    }
}
