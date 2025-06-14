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
use Phayne\Saml\Model\Metadata\EntityDescriptor;

/**
 * Class CompositeEntityDescriptorStore
 *
 * @package Phayne\Saml\Store\EntityDescriptor
 */
class CompositeEntityDescriptorStore implements EntityDescriptorStoreInterface
{
    public function __construct(private array $children = [])
    {
    }

    public function add(EntityDescriptorStoreInterface $store): CompositeEntityDescriptorStore
    {
        $this->children[] = $store;
        return $this;
    }

    #[Override]
    public function get(string $entityId): ?EntityDescriptor
    {
        foreach ($this->children as $store) {
            $descriptor = $store->get($entityId);

            if (null !== $descriptor) {
                return $descriptor;
            }
        }

        return null;
    }

    #[Override]
    public function has(string $entityId): bool
    {
        return array_any($this->children, fn (EntityDescriptorStoreInterface $store) => $store->has($entityId));
    }

    #[Override]
    public function all(): array
    {
        $all = [];

        foreach ($this->children as $store) {
            $all = array_merge($all, $store->all());
        }

        return $all;
    }
}
