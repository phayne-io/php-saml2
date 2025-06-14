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
use Phayne\Saml\Model\Assertion\Conditions;
use Phayne\Saml\Model\Assertion\Subject;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

use function strcasecmp;

/**
 * Class AuthnRequest
 *
 * @package Phayne\Saml\Model\Protocol
 */
class AuthnRequest extends AbstractRequest
{
    protected(set) ?bool $forceAuthn = null {
        set(bool | string | int | null $value) {
            $this->forceAuthn = 0 == strcasecmp($value, 'true') || true === $value || 1 == $value;
        }
    }

    protected(set) ?bool $isPassive = null {
        set(bool | string | int | null $value) {
            $this->isPassive = 0 == strcasecmp($value, 'true') || true === $value || 1 == $value;
        }
    }

    protected(set) ?int $assertionConsumerServiceIndex = null;

    protected(set) ?string $assertionConsumerServiceURL = null;

    protected(set) ?int $assertionConsumingServiceIndex = null;

    protected(set) ?string $protocolBinding = null;

    protected(set) ?string $providerName = null;

    public function isPassiveString(): ?string
    {
        if (null === $this->isPassive) {
            return null;
        }

        return $this->isPassive ? 'true' : 'false';
    }

    public function forceAuthnString(): ?string
    {
        if (null === $this->forceAuthn) {
            return null;
        }

        return $this->forceAuthn ? 'true' : 'false';
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('AuthnRequest', SamlConstant::PROTOCOL_SAML2->value, $parent, $context);
        parent::serialize($element, $context);

        $this->attributesToXml([
            'ForceAuthn', 'IsPassive', 'ProtocolBinding', 'AssertionConsumerServiceIndex',
            'AssertionConsumerServiceURL', 'AttributeConsumingServiceIndex', 'ProviderName',
        ], $element);
        $this->singleElementsToXml(['Subject', 'NameIDPolicy', 'Conditions'], $element, $context);
        $this->singleElementsToXml(['Signature'], $element, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'AuthnRequest', SamlConstant::PROTOCOL_SAML2);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, [
            'ForceAuthn', 'IsPassive', 'ProtocolBinding', 'AssertionConsumerServiceIndex',
            'AssertionConsumerServiceURL', 'AttributeConsumingServiceIndex', 'ProviderName',
        ]);

        $this->singleElementsFromXml($node, $context, [
            'Subject' => ['saml', Subject::class],
            'NameIDPolicy' => ['samlp', NameIDPolicy::class],
            'Conditions' => ['saml', Conditions::class],
        ]);
    }
}
