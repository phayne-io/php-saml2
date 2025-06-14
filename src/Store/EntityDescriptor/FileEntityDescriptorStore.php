<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\EntityDescriptor;

use Override;
use Phayne\Saml\Exception\SamlXmlException;
use Phayne\Saml\Model\Metadata\EntitiesDescriptor;
use Phayne\Saml\Model\Metadata\EntityDescriptor;

/**
 * Class FileEntityDescriptorStore
 *
 * @package Phayne\Saml\Store\EntityDescriptor
 */
class FileEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    private EntityDescriptor|EntitiesDescriptor|null $object = null;

    public function __construct(private readonly string $filename)
    {
    }

    #[Override]
    public function get(string $entityId): ?EntityDescriptor
    {
        if (null === $this->object) {
            $this->load();
        }

        if ($this->object instanceof EntityDescriptor) {
            if ($this->object->entityID == $entityId) {
                return $this->object;
            } else {
                return null;
            }
        } else {
            return $this->object->entityById($entityId);
        }
    }

    #[Override]
    public function has(string $entityId): bool
    {
        return null !== $this->get($entityId);
    }

    #[Override]
    public function all(): array
    {
        if (null == $this->object) {
            $this->load();
        }

        if ($this->object instanceof EntityDescriptor) {
            return [$this->object];
        } else {
            return $this->object->entityDescriptors();
        }
    }

    private function load(): void
    {
        try {
            $this->object = EntityDescriptor::load($this->filename);
        } catch (SamlXmlException) {
            $this->object = EntitiesDescriptor::load($this->filename);
        }
    }
}
