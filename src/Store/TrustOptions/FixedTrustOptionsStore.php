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
 * Class FixedTrustOptionsStore
 *
 * @package Phayne\Saml\Store\TrustOptions
 */
class FixedTrustOptionsStore implements TrustOptionsStoreInterface
{
    public function __construct(protected ?TrustOptions $option = null)
    {
    }

    public function withTrustOptions(?TrustOptions $trustOptions = null): TrustOptionsStoreInterface
    {
        $clone = clone $this;
        $clone->option = $trustOptions;
        return $clone;
    }

    #[Override]
    public function get(string $entityId): ?TrustOptions
    {
        return $this->option;
    }

    #[Override]
    public function has(string $entityId): bool
    {
        return true;
    }
}
