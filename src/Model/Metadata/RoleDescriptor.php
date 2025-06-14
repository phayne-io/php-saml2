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
use Phayne\Saml\Model\AbstractSamlModel;
use Phayne\Saml\Model\Context;
use Phayne\Saml\Model\XmlDSig\Signature;
use Phayne\Saml\SamlConstant;

/**
 * Class RoleDescriptor
 *
 * @package Phayne\Saml\Model\Metadata
 */
abstract class RoleDescriptor extends AbstractSamlModel
{
    protected(set) ?string $id = null;

    protected(set) ?string $errolURL = null;

    protected(set) ?string $cacheDuration = null {
        set(null|string $value) {
            Helper::validateDurationString($value);
            $this->cacheDuration = $value;
        }
    }

    protected(set) ?int $validUntil = null {
        set(int|null $value) {
            $this->validUntil = Helper::getTimestampFromValue($value);
        }
    }

    /**
     * @var ContactPerson[]
     */
    protected(set) array $contactPersons = [];

    /**
     * @var KeyDescriptor[]
     */
    protected(set) array $keyDescriptors = [];

    /**
     * @var Organization[]
     */
    protected(set) array $organizations = [];

    protected(set) array $signatures = [];

    protected(set) SamlConstant $protocolSupportEnumeration = SamlConstant::PROTOCOL_SAML2;

    public function validUntilString(): ?string
    {
        return $this->validUntil
            ? Helper::time2string($this->validUntil)
            : null;
    }

    public function addSignature(Signature $signature): RoleDescriptor
    {
        $this->signatures[] = $signature;
        return $this;
    }

    public function addContactPerson(ContactPerson $contactPerson): RoleDescriptor
    {
        $this->contactPersons[] = $contactPerson;
        return $this;
    }

    public function addOrganization(Organization $organization): RoleDescriptor
    {
        $this->organizations[] = $organization;
        return $this;
    }

    public function addKeyDescriptor(KeyDescriptor $keyDescriptor): RoleDescriptor
    {
        $this->keyDescriptors[] = $keyDescriptor;
        return $this;
    }

    public function keyDescriptorsByUse(string $use): array
    {
        return array_filter($this->keyDescriptors, fn(RoleDescriptor $d) => $d->use === $use);
    }

    public function firstKeyDescriptor(?string $use = null): ?KeyDescriptor
    {
        return array_find($this->keyDescriptors, fn(KeyDescriptor $d) => $use === null || $d->use === $use);
    }

    #[Override]
    public function serialize(DOMNode|DOMElement $parent, Context\SerializationContext $context): void
    {
        $this->attributesToXml(
            ['protocolSupportEnumeration', 'ID', 'validUntil', 'cacheDuration', 'errorURL'],
            $parent
        );

        $this->manyElementsToXml($this->signatures, $parent, $context);
        $this->manyElementsToXml($this->keyDescriptors, $parent, $context);
        $this->manyElementsToXml($this->organizations, $parent, $context);
        $this->manyElementsToXml($this->contactPersons, $parent, $context);
    }

    #[Override]
    public function deserialize(DOMNode|DOMElement $node, Context\DeserializationContext $context): void
    {
        $this->attributesFromXml(
            $node,
            ['protocolSupportEnumeration', 'ID', 'validUntil', 'cacheDuration', 'errorURL']
        );

        $this->manyElementsFromXml(
            $node,
            $context,
            'Signature',
            'ds',
            Signature::class,
            'addSignature'
        );
        $this->manyElementsFromXml(
            $node,
            $context,
            'KeyDescriptor',
            'md',
            KeyDescriptor::class,
            'addKeyDescriptor'
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
    }
}
