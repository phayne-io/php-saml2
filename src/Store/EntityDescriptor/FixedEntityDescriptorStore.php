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

use InvalidArgumentException;
use Override;
use Phayne\Saml\Model\Metadata\EntitiesDescriptor;
use Phayne\Saml\Model\Metadata\EntityDescriptor;

/**
 * Class FixedEntityDescriptorStore
 *
 * @package Phayne\Saml\Store\EntityDescriptor
 */
class FixedEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    protected array $descriptors = [];

    public function add(EntityDescriptor|EntitiesDescriptor $entityDescriptor): FixedEntityDescriptorStore
    {
        if ($entityDescriptor instanceof EntityDescriptor) {
            if (null === $entityDescriptor->entityID) {
                throw new InvalidArgumentException('EntityDescriptor must have entityId set');
            }
            $this->descriptors[$entityDescriptor->entityID] = $entityDescriptor;
        } else {
            foreach ($entityDescriptor->items as $item) {
                $this->add($item);
            }
        }

        return $this;
    }

    #[Override]
    public function get(string $entityId): ?EntityDescriptor
    {
        return $this->descriptors[$entityId] ?? null;
    }

    #[Override]
    public function has(string $entityId): bool
    {
        return isset($this->descriptors[$entityId]);
    }

    #[Override]
    public function all(): array
    {
        return array_values($this->descriptors);
    }
}
