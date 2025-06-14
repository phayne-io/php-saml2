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
use InvalidArgumentException;
use Override;
use Phayne\Saml\Helper;
use Phayne\Saml\Model\Context;
use Phayne\Saml\Model\XmlDSig\Signature;
use Phayne\Saml\Model\XmlDSig\SignatureXmlReader;
use Phayne\Saml\SamlConstant;

use function array_any;
use function array_merge;
use function file_get_contents;

/**
 * Class EntitiesDescriptor
 *
 * @package Phayne\Saml\Model\Metadata
 */
class EntitiesDescriptor extends Metadata
{
    protected(set) ?int $validUntil = null {
        set(int|null $value) {
            if (Helper::getTimestampFromValue($value) < 0) {
                throw new InvalidArgumentException('Invalid timestamp value for validUntil');
            }
            $this->validUntil = $value;
        }
    }

    protected(set) string $cacheDuration {
        set(string $value) {
            Helper::validateDurationString($value);
            $this->cacheDuration = $value;
        }
    }

    protected(set) string $id;

    protected(set) string $name;

    protected(set) Signature $signature;

    protected(set) array $items = [];

    public static function load(string $filename): EntitiesDescriptor
    {
        return self::load(@file_get_contents($filename));
    }

    public static function loadXml(string $xml): EntitiesDescriptor
    {
        $context = new Context\DeserializationContext();
        $context->document->loadXML($xml);
        $self = new self();
        $self->deserialize($context->document, $context);
        return $self;
    }

    public function validUntilString(): ?string
    {
        return null === $this->validUntil
            ? null
            : Helper::time2string($this->validUntil);
    }

    public function addItem(EntitiesDescriptor | EntityDescriptor $item): EntitiesDescriptor
    {
        if ($item === $this || ($item instanceof self && $item->containsItem($this))) {
            throw new InvalidArgumentException('Circular reference detected');
        }

        $this->items[] = $item;
        return $this;
    }

    public function containsItem(EntitiesDescriptor | EntityDescriptor $item): bool
    {
        return array_any($this->items, fn ($i) => $item === $i || ($i instanceof self && $i->containsItem($item)));
    }

    public function entityDescriptors(): array
    {
        $descriptors = [];

        foreach ($this->items as $item) {
            if ($item instanceof self) {
                $descriptors = array_merge($descriptors, $item->entityDescriptors());
            } else {
                $descriptors[] = $item;
            }
        }

        return $descriptors;
    }

    public function entityById(string $entityId): ?EntityDescriptor
    {
        return array_find(
            $this->entityDescriptors(),
            fn ($entityDescriptor) => $entityDescriptor->entityID === $entityId
        );
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $element = $this->createElement('EntitiesDescriptor', SamlConstant::NS_METADATA, $parent, $context);
        $this->attributesToXml(['validUntil', 'cacheDuration', 'ID', 'Name'], $element);
        $this->singleElementsToXml(['Signature'], $element, $context);
        $this->manyElementsToXml($this->items, $element, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'EntitiesDescriptor', SamlConstant::NS_METADATA);
        $this->attributesFromXml($node, ['validUntil', 'cacheDuration', 'ID', 'Name']);
        $this->singleElementsFromXml($node, $context, [
            'Signature' => ['ds', SignatureXmlReader::class],
        ]);
        $this->manyElementsFromXml(
            $node,
            $context,
            'EntityDescriptor',
            'md',
            EntityDescriptor::class,
            'addItem'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'EntitiesDescriptor',
            'md',
            EntitiesDescriptor::class,
            'addItem'
        );
    }
}
