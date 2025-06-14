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
 * Class SpSsoDescriptor
 *
 * @package Phayne\Saml\Model\Metadata
 */
class SpSsoDescriptor extends SSODescriptor
{
    protected(set) ?bool $authnRequestsSigned = null;

    protected(set) ?bool $wantAssertionsSigned = null;

    protected(set) array $assertionConsumerServices = [];

    public function addAssertionConsumerService(AssertionConsumerService $assertionConsumerService): SpSsoDescriptor
    {
        if (null === $assertionConsumerService->index) {
            $assertionConsumerService->index = count($this->assertionConsumerServices);
        }

        $this->assertionConsumerServices[] = $assertionConsumerService;
        return $this;
    }

    public function assertionConsumerServicesByBinding(string $binding): array
    {
        return array_filter(
            $this->assertionConsumerServices,
            fn (AssertionConsumerService $assertionConsumerService) => $assertionConsumerService->binding === $binding
        );
    }

    public function assertionConsumerServicesByUrl(string $url): array
    {
        return array_filter(
            $this->assertionConsumerServices,
            fn (AssertionConsumerService $assertionConsumerService) => $assertionConsumerService->location === $url
        );
    }

    public function assertionConsumerServicesByIndex(int $index): array
    {
        return array_filter(
            $this->assertionConsumerServices,
            fn (AssertionConsumerService $assertionConsumerService) => $assertionConsumerService->index === $index
        );
    }

    public function firstAssertionConsumerService(?string $binding = null): ?AssertionConsumerService
    {
        return array_find(
            $this->assertionConsumerServices,
            fn(AssertionConsumerService $assertionConsumerService) =>
                null === $binding &&
                $assertionConsumerService->binding === $binding
        );
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('SPSSODescriptor', SamlConstant::NS_METADATA, $parent, $context);

        parent::serialize($element, $context);

        $this->attributesToXml(['AuthnRequestsSigned', 'WantAssertionsSigned'], $element);
        $this->manyElementsToXml($this->assertionConsumerServices, $element, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'SPSSODescriptor', SamlConstant::NS_METADATA);

        parent::deserialize($node, $context);

        $this->attributesFromXml($node, ['AuthnRequestsSigned', 'WantAssertionsSigned']);
        $this->manyElementsFromXml(
            $node,
            $context,
            'AssertionConsumerService',
            'md',
            AssertionConsumerService::class,
            'addAssertionConsumerService'
        );
    }
}
