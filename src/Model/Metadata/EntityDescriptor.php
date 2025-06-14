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
use Phayne\Saml\Helper;
use Phayne\Saml\Model\Context;
use Phayne\Saml\Model\XmlDSig\Signature;
use Phayne\Saml\Model\XmlDSig\SignatureXmlReader;
use Phayne\Saml\SamlConstant;

use function array_filter;
use function array_find;
use function array_map;
use function file_get_contents;

/**
 * Class EntityDescriptor
 *
 * @package Phayne\Saml\Model\Metadata
 */
class EntityDescriptor extends Metadata
{
    protected(set) ?int $validUntil = null {
        set(int | null $value) {
            if (null !== $value) {
                $this->validUntil = Helper::getTimestampFromValue($value);
            }
        }
    }

    protected(set) ?string $cacheDuration = null {
        set(null | string $value) {
            Helper::validateDurationString($value);
            $this->cacheDuration = $value;
        }
    }

    protected(set) ?string $id = null;

    protected(set) ?Signature $signature = null;

    protected(set) array $organizations = [];

    protected(set) array $contactPersons = [];

    public static function load(string $filename): EntityDescriptor
    {
        return self::loadXml(@file_get_contents($filename));
    }

    public static function loadXml(string $xml): EntityDescriptor
    {
        $context = new Context\DeserializationContext();
        $context->document->loadXML($xml);
        $self = new self();
        $self->deserialize($context->document, $context);
        return $self;
    }

    public function __construct(protected(set) ?string $entityID = null, protected(set) array $items = [])
    {
    }

    public function addContactPerson(ContactPerson $contactPerson): EntityDescriptor
    {
        $this->contactPersons[] = $contactPerson;
        return $this;
    }

    public function firstContactPerson(): ?ContactPerson
    {
        return $this->contactPersons[0] ?? null;
    }

    public function addOrganization(Organization $organization): EntityDescriptor
    {
        $this->organizations[] = $organization;
        return $this;
    }

    public function firstOrganization(): ?Organization
    {
        return $this->organizations[0] ?? null;
    }

    public function addItem(IdpSsoDescriptor|SpSsoDescriptor $item): EntityDescriptor
    {
        $this->items[] = $item;
        return $this;
    }

    public function idpSsoDescriptors(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof IdpSsoDescriptor);
    }

    public function spSsoDescriptors(): array
    {
        return array_filter($this->items, fn($item) => $item instanceof SpSsoDescriptor);
    }

    public function firstIdpSsoDescriptor(): ?IdpSsoDescriptor
    {
        return array_find($this->items, fn($item) => $item instanceof IdpSsoDescriptor);
    }

    public function firstSpSsoDescriptor(): ?SpSsoDescriptor
    {
        return array_find($this->items, fn($item) => $item instanceof SpSsoDescriptor);
    }

    public function validUntilString(): ?string
    {
        return $this->validUntil === null
            ? null
            : Helper::time2string($this->validUntil);
    }

    public function idpKeyDescriptors(): array
    {
        return array_map(fn (IdpSsoDescriptor $item) => $item->keyDescriptors, $this->idpSsoDescriptors());
    }

    public function spKeyDescriptors(): array
    {
        return array_map(fn (SpSsoDescriptor $item) => $item->keyDescriptors, $this->spSsoDescriptors());
    }

    public function endpoints(): array
    {
        $endpoints = [];

        /** @var IdpSsoDescriptor $idpSsoDescriptor */
        foreach ($this->idpSsoDescriptors() as $idpSsoDescriptor) {
            foreach ($idpSsoDescriptor->singleSignOnServices as $sso) {
                $endpoints[] = new EndpointReference($this, $idpSsoDescriptor, $sso);
            }
            foreach ($idpSsoDescriptor->singleLogoutServices as $slo) {
                $endpoints[] = new EndpointReference($this, $idpSsoDescriptor, $slo);
            }
        }

        /** @var SpSsoDescriptor $spSsoDescriptor */
        foreach ($this->spSsoDescriptors() as $spSsoDescriptor) {
            foreach ($spSsoDescriptor->assertionConsumerServices as $acs) {
                $endpoints[] = new EndpointReference($this, $spSsoDescriptor, $acs);
            }
            foreach ($spSsoDescriptor->singleLogoutServices as $slo) {
                $endpoints[] = new EndpointReference($this, $spSsoDescriptor, $slo);
            }
        }

        return $endpoints;
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $result = $this->createElement('EntityDescriptor', SamlConstant::NS_METADATA, $parent, $context);

        $this->attributesToXml(['entityID', 'validUntil', 'cacheDuration', 'ID'], $result);

        $this->manyElementsToXml($this->items, $result, $context);
        if ($this->organizations) {
            $this->manyElementsToXml($this->organizations, $result, $context);
        }
        if ($this->contactPersons) {
            $this->manyElementsToXml($this->contactPersons, $result, $context);
        }

        $this->singleElementsToXml(['Signature'], $result, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->checkXmlNodeName($node, 'EntityDescriptor', SamlConstant::NS_METADATA);
        $this->attributesFromXml($node, ['entityID', 'validUntil', 'cacheDuration', 'ID']);
        $this->items = [];

        $this->manyElementsFromXml(
            $node,
            $context,
            'IDPSSODescriptor',
            'md',
            IdpSsoDescriptor::class,
            'addItem'
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'SPSSODescriptor',
            'md',
            SpSsoDescriptor::class,
            'addItem'
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'Organization',
            'md',
            Organization::class,
            'addOrganization'
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'ContactPerson',
            'md',
            ContactPerson::class,
            'addContactPerson'
        );

        $this->singleElementsFromXml($node, $context, [
            'Signature' => ['ds', SignatureXmlReader::class],
        ]);
    }
}
