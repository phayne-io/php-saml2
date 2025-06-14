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

use DateTime;
use DOMElement;
use DOMNode;
use Override;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\SamlConstant;

/**
 * Class Conditions
 *
 * @package Phayne\Saml\Model\Assertion
 */
class Conditions extends AbstractSamlModel
{
    protected(set) ?int $notBefore = null {
        set(int | string | DateTime | null $value) {
            $this->notBefore = Helper::getTimestampFromValue($value);
        }
    }

    protected(set) ?int $notOnOrAfter = null {
        set(int | string | DateTime | null $value) {
            $this->notOnOrAfter = Helper::getTimestampFromValue($value);
        }
    }

    protected(set) array $items = [];

    public function addItem(AbstractCondition $item): Conditions
    {
        $this->items[] = $item;
        return $this;
    }

    public function allAudienceRestrictions(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof AudienceRestriction);
    }

    public function firstAudienceRestrictions(): ?AudienceRestriction
    {
        return array_find($this->items, fn($item) => $item instanceof AudienceRestriction);
    }

    public function allOneTimeUses(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof OneTimeUse);
    }

    public function firstOneTimeUse(): ?OneTimeUse
    {
        return array_find($this->items, fn($item) => $item instanceof OneTimeUse);
    }

    public function allProxyRestrictions(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof ProxyRestriction);
    }

    public function firstProxyRestrictions(): ?ProxyRestriction
    {
        return array_find($this->items, fn($item) => $item instanceof ProxyRestriction);
    }

    public function notBeforeString(): ?string
    {
        if (null !== $this->notBefore) {
            return Helper::time2string($this->notBefore);
        }

        return null;
    }

    public function notOnOrAfterString(): ?string
    {
        if (null !== $this->notOnOrAfter) {
            return Helper::time2string($this->notOnOrAfter);
        }

        return null;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('Conditions', SamlConstant::NS_ASSERTION, $parent, $context);

        $this->attributesToXml(
            ['NotBefore', 'NotOnOrAfter'],
            $result
        );

        foreach ($this->items as $item) {
            $item->serialize($result, $context);
        }
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'Conditions', SamlConstant::NS_ASSERTION);

        $this->attributesFromXml($node, ['NotBefore', 'NotOnOrAfter']);

        $this->manyElementsFromXml(
            $node,
            $context,
            'AudienceRestriction',
            'saml',
            AudienceRestriction::class,
            'addItem'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'OneTimeUse',
            'saml',
            OneTimeUse::class,
            'addItem'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'ProxyRestriction',
            'saml',
            ProxyRestriction::class,
            'addItem'
        );
    }
}
