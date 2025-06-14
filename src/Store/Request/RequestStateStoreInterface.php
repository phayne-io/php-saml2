<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Store\Request;

use Phayne\Saml\State\Request\RequestState;

/**
 * Interface RequestStateStoreInterface
 *
 * @package Phayne\Saml\Store\Request
 */
interface RequestStateStoreInterface
{
    public function set(RequestState $state): RequestStateStoreInterface;

    public function get(string $id): ?RequestState;

    public function remove(string $id): bool;

    public function clear(): void;
}
