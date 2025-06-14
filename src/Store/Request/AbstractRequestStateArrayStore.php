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
use Override;

/**
 * Class AbstractRequestStateArrayStore
 *
 * @package Phayne\Saml\Store\Request
 */
abstract class AbstractRequestStateArrayStore implements RequestStateStoreInterface
{
    protected array $array = [] {
        get {
            return $this->array;
        }
        set(array $value) {
            $this->array = $value;
        }
    }

    #[Override]
    public function set(RequestState $state): RequestStateStoreInterface
    {
        $arr = $this->array;
        $arr[$state->id] = $state;
        $this->array = $arr;
        return $this;
    }

    #[Override]
    public function get(string $id): ?RequestState
    {
        return $this->array[$id] ?? null;
    }

    #[Override]
    public function remove(string $id): bool
    {
        if (isset($this->array[$id])) {
            unset($this->array[$id]);
            return true;
        }

        return false;
    }

    #[Override]
    public function clear(): void
    {
        $this->array = [];
    }
}
