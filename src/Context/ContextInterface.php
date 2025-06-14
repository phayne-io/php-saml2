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

use IteratorAggregate;

/**
 * Interface ContextInterface
 *
 * @package Phayne\Saml\Context
 * @template <T>
 * @extends IteratorAggregate<T>
 */
interface ContextInterface extends IteratorAggregate
{
    public ?ContextInterface $parent = null {
        get;
        set;
    }

    public function topParent(): ContextInterface;

    public function subContext(string $name, ?string $class = null): ?ContextInterface;

    public function subContextByClass(string $class, bool $autoCreate): ?ContextInterface;

    public function addSubContext(string $name, object $subContext): ContextInterface;

    public function removeSubContext(string $name): ContextInterface;

    public function containsSubContext(string $name): bool;

    public function clearSubContexts(): ContextInterface;

    public function debugPrintTree(string $ownName = 'root'): array;

    public function path(string $path): ?ContextInterface;
}
