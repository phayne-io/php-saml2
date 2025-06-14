<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Meta;

use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use Override;
use Serializable;
use Traversable;

use function array_key_exists;
use function count;
use function serialize;
use function unserialize;

/**
 * Class ParameterBag
 *
 * @template T
 * @implements IteratorAggregate<T>
 * @package Phayne\Saml\Meta
 */
class ParameterBag implements IteratorAggregate, Countable, Serializable
{
    public function __construct(protected array $parameters = [])
    {
    }

    public function all(): array
    {
        return $this->parameters;
    }

    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    public function replace(array $parameters = []): void
    {
        $this->parameters = $parameters;
    }

    public function add(array $parameters = []): void
    {
        $this->parameters = array_replace($this->parameters, $parameters);
    }

    public function get($key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->parameters) ? $this->parameters[$key] : $default;
    }

    public function set($key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->parameters);
    }

    public function remove(string $key): void
    {
        unset($this->parameters[$key]);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->parameters);
    }

    #[Override]
    public function count(): int
    {
        return count($this->parameters);
    }

    #[Override]
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function __serialize(): array
    {
        return $this->parameters;
    }

    #[Override]
    public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    public function __unserialize(array $data): void
    {
        $this->parameters = $data;
    }
}
