<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\TrustOptions;

use Override;
use Phayne\Saml\Meta\TrustOptions\TrustOptions;

/**
 * Class CompositeTrustOptionsStore
 *
 * @package Phayne\Saml\Store\TrustOptions
 */
class CompositeTrustOptionsStore implements TrustOptionsStoreInterface
{
   public function __construct(private array $children = [])
   {
   }

    public function add(TrustOptionsStoreInterface $store): CompositeTrustOptionsStore
    {
        $this->children[] = $store;
        return $this;
    }

    #[Override]
    public function get(string $entityId): ?TrustOptions
    {
        return array_find(
            $this->children,
            fn (TrustOptionsStoreInterface $store) => $store->get($entityId) ?? null
        );
    }

    #[Override]
    public function has(string $entityId): bool
    {
        return array_any($this->children, fn (TrustOptionsStoreInterface $store) => $store->has($entityId));
    }
}
