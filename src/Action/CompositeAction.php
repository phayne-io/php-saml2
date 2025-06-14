<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Action;

use Override;
use Phayne\Saml\Context\ContextInterface;
use Stringable;

use function array_merge;
use function call_user_func;
use function json_encode;

use const JSON_PRETTY_PRINT;

/**
 * Class CompositeAction
 *
 * @package Phayne\Saml\Action
 */
class CompositeAction implements Stringable, DebugPrintTreeActionInterface, CompositeActionInterface
{
    public function __construct(protected(set) array $children = [])
    {
    }

    #[Override]
    public function add(ActionInterface $action): CompositeActionInterface
    {
        $this->children[] = $action;
        return $this;
    }

    #[Override]
    public function map(callable $callable): void
    {
        foreach ($this->children as $key => $action) {
            $newAction = call_user_func($callable, $action);
            if ($newAction) {
                $this->children[$key] = $action;
            }
        }
    }

    #[Override]
    public function execute(ContextInterface $context): void
    {
        foreach ($this->children as $action) {
            $action->execute($context);
        }
    }

    #[Override]
    public function debugPrintTree(): array
    {
        $tree = [];

        foreach ($this->children as $action) {
            if ($action instanceof DebugPrintTreeActionInterface) {
                $tree = array_merge($tree, $action->debugPrintTree());
            } else {
                $tree = array_merge($tree, [$action::class => []]);
            }
        }

        return [
            static::class => $tree,
        ];
    }

    #[Override]
    public function __toString(): string
    {
        return (string)json_encode($this->debugPrintTree(), JSON_PRETTY_PRINT);
    }
}
