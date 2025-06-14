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
 * Class SSODescriptor
 *
 * @package Phayne\Saml\Model\Metadata
 */
abstract class SSODescriptor extends RoleDescriptor
{
    /**
     * @var SingleLogoutService[]
     */
    protected(set) array $singleLogoutServices = [];

    protected(set) ?array $nameIdFormats = null;

    public function addSingleLogoutService(SingleLogoutService $singleLogoutService): SSODescriptor
    {
        $this->singleLogoutServices[] = $singleLogoutService;
        return $this;
    }

    public function singleLogoutServicesByBinding(string $binding): array
    {
        return array_filter(
            $this->singleLogoutServices,
            fn ($singleLogoutService) => $singleLogoutService->binding === $binding
        );
    }

    public function firstSingleLogoutService(?string $binding = null): ?SingleLogoutService
    {
        return array_find(
            $this->singleLogoutServices,
            fn ($singleLogoutService) => $singleLogoutService->binding === $binding || null === $binding
        );
    }

    public function addNameIDFormat(string $nameIDFormat): SSODescriptor
    {
        $this->nameIdFormats[] = $nameIDFormat;
        return $this;
    }

    public function hasNameIDFormat(string $nameIdFormat): bool
    {
        return array_any($this->nameIdFormats, fn ($nameIDFormat) => $nameIDFormat === $nameIdFormat);
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        parent::serialize($parent, $context);
        $this->manyElementsToXml($this->singleLogoutServices, $parent, $context);
        $this->manyElementsToXml($this->nameIdFormats, $parent, $context, 'NameIDFormat', SamlConstant::NS_METADATA);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        parent::deserialize($node, $context);

        $this->manyElementsFromXml($node, $context, 'NameIDFormat', 'md', null, 'addNameIDFormat');

        $this->manyElementsFromXml(
            $node,
            $context,
            'SingleLogoutService',
            'md',
            SingleLogoutService::class,
            'addSingleLogoutService'
        );
    }
}
