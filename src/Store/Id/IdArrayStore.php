<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\Id;

use DateTime;
use Override;
use Phayne\Saml\Store\Id\IdStoreInterface;

/**
 * Class IdArrayStore
 *
 * @package Phayne\Saml\Store\Id
 */
class IdArrayStore implements IdStoreInterface
{
    protected array $store = [];

    #[Override]
    public function set(string $entityId, string $id, DateTime $expiryTime): void
    {
        if (false === isset($this->store[$entityId])) {
            $this->store[$entityId] = [];
        }
        $this->store[$entityId][$id] = $expiryTime;
    }

    #[Override]
    public function has(string $entityId, string $id): bool
    {
        return isset($this->store[$entityId][$id]);
    }
}
