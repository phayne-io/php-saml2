<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Context;

use ArrayIterator;
use BackedEnum;
use InvalidArgumentException;
use Override;
use Stringable;

use function array_merge;
use function array_shift;
use function explode;
use function is_array;
use function is_string;
use function json_encode;

use const JSON_PRETTY_PRINT;

/**
 * Class AbstractContext
 *
 * @package Phayne\Saml\Context
 * @template T
 * @extends ContextInterface<T>
 */
class AbstractContext implements ContextInterface, Stringable
{
    public ?ContextInterface $parent = null;

    protected(set) array $subContexts = [];

    #[Override]
    public function topParent(): ContextInterface
    {
        if ($this->parent !== null) {
            return $this->parent->topParent();
        }

        return $this;
    }

    #[Override]
    public function subContext(string|BackedEnum $name, ?string $class = null): ?ContextInterface
    {
        if ($name instanceof BackedEnum) {
            $name = $name->value;
        }

        if (isset($this->subContexts[$name])) {
            return $this->subContexts[$name];
        }

        if (null !== $class) {
            $result = $this->createSubContext($class);
            $this->addSubContext($name, $result);

            return $result;
        }

        return null;
    }

    #[Override]
    public function subContextByClass(string $class, bool $autoCreate): ?ContextInterface
    {
        return $this->subContext($class, $autoCreate ? $class : null);
    }

    #[Override]
    public function addSubContext(string $name, object $subContext): AbstractContext
    {
        $existing = $this->subContexts[$name] ?? null;

        if ($existing === $subContext) {
            return $this;
        }

        $this->subContexts[$name] = $subContext;

        if ($subContext instanceof ContextInterface) {
            $subContext->parent = $this;
        }

        if ($existing instanceof ContextInterface) {
            $existing->parent = null;
        }

        return $this;
    }

    #[Override]
    public function removeSubContext(string $name): ContextInterface
    {
        $subContext = $this->subContext($name);

        if ($subContext) {
            $subContext->parent = null;
            unset($this->subContexts[$name]);
        }

        return $this;
    }

    #[Override]
    public function containsSubContext(string $name): bool
    {
        return isset($this->subContexts[$name]);
    }

    #[Override]
    public function clearSubContexts(): ContextInterface
    {
        foreach ($this->subContexts as $subContext) {
            $subContext->setParent(null);
        }

        $this->subContexts = [];

        return $this;
    }

    #[Override]
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->subContexts);
    }

    #[Override]
    public function debugPrintTree(string $ownName = 'root'): array
    {
        $result = [
            $ownName => static::class,
        ];

        if ($this->subContexts) {
            $arr = [];
            foreach ($this->subContexts as $name => $subContext) {
                if ($subContext instanceof ContextInterface) {
                    $arr = array_merge($arr, $subContext->debugPrintTree($name));
                } else {
                    $arr = array_merge($arr, [$name => $subContext::class]);
                }
            }
            $result[$ownName . '__children'] = $arr;
        }

        return $result;
    }

    #[Override]
    public function path($path): ?ContextInterface
    {
        if (is_string($path)) {
            $path = explode('/', $path);
        } elseif (false === is_array($path)) {
            throw new InvalidArgumentException('Expected string or array');
        }

        $name = array_shift($path);
        $subContext = $this->subContext($name);

        if (null == $subContext) {
            return null;
        }

        return $path === []
            ? $subContext
            : $subContext->path($path);
    }

    #[Override]
    public function __toString(): string
    {
        return (string)json_encode($this->debugPrintTree(), JSON_PRETTY_PRINT);
    }

    protected function createSubContext(string $class): ContextInterface
    {
        return new $class();
    }
}
