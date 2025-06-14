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
use Phayne\Saml\Model\Assertion\Attribute;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class IdpSsoDescriptor
 *
 * @package Phayne\Saml\Model\Metadata
 */
class IdpSsoDescriptor extends SSODescriptor
{
    protected(set) ?bool $authnRequestsSigned = null;

    protected(set) array $singleSignOnServices = [];

    protected(set) array $attributes = [];

    public function addSingleSignOnService(SingleSignOnService $singleSignOnService): IdpSsoDescriptor
    {
        $this->singleSignOnServices[] = $singleSignOnService;
        return $this;
    }

    public function singleSignOnServicesByUrl(string $url): array
    {
        return array_filter(
            $this->singleSignOnServices,
            fn (SingleSignOnService $singleSignOnService) => $singleSignOnService->location === $url
        );
    }

    public function singleSignOnServicesByBinding(string $binding): array
    {
        return array_filter(
            $this->singleSignOnServices,
            fn (SingleSignOnService $singleSignOnService) => $singleSignOnService->binding === $binding
        );
    }

    public function firstSingleSignOnService(?string $binding = null): ?SingleSignOnService
    {
        return array_find(
            $this->singleSignOnServices,
            fn (SingleSignOnService $singleSignOnService) =>
                null === $binding ||
                $singleSignOnService->binding === $binding
        );
    }

    public function addAttribute(Attribute $attribute): IdpSsoDescriptor
    {
        $this->attributes[] = $attribute;
        return $this;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('IDPSSODescriptor', SamlConstant::NS_METADATA, $parent, $context);

        parent::serialize($element, $context);

        $this->attributesToXml(['WantAuthnRequestsSigned'], $element);

        foreach ($this->singleSignOnServices as $object) {
            $object->serialize($element, $context);
        }
        foreach ($this->attributes as $object) {
            $object->serialize($element, $context);
        }
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'IDPSSODescriptor', SamlConstant::NS_METADATA);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, ['WantAuthnRequestsSigned']);

        $this->singleSignOnServices = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'SingleSignOnService',
            'md',
            SingleSignOnService::class,
            'addSingleSignOnService'
        );

        $this->attributes = [];
        $this->manyElementsFromXml(
            $node,
            $context,
            'Attribute',
            'saml',
            Attribute::class,
            'addAttribute'
        );
    }
}
