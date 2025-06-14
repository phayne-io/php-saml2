<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Criteria;

use function array_any;
use function array_filter;
use function array_find;
use function call_user_func;

/**
 * Class CriteriaSet
 *
 * @package Phayne\Saml\Criteria
 */
class CriteriaSet
{
    public function __construct(protected array $criterion = [])
    {
    }

    public function add(CriteriaInterface $criteria): CriteriaSet
    {
        $this->criterion[] = $criteria;
        return $this;
    }

    public function addIf(mixed $condition, callable $callback): CriteriaSet
    {
        if ($condition) {
            $criteria = call_user_func($callback);
            if ($criteria) {
                $this->add($criteria);
            }
        }

        return $this;
    }

    public function all(): array
    {
        return $this->criterion;
    }

    public function get(string $class): array
    {
        return array_filter($this->criterion, fn ($criteria) => $criteria instanceof $class);
    }

    public function getSingle(string $class): ?CriteriaInterface
    {
        return array_find($this->criterion, fn ($criteria) => $criteria instanceof $class);
    }

    public function has(string $class): bool
    {
        return array_any($this->criterion, fn ($criteria) => $criteria instanceof $class);
    }
}
