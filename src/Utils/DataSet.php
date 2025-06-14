<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Utils;

use Override;
use Stringable;

use function array_key_exists;

/**
 * Class DataSet
 *
 * @package Phayne\Saml\Utils
 */
final readonly class DataSet implements Stringable
{
    public function __construct(private array $data)
    {
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->data);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function toString(): string
    {
        return json_encode($this->data) ?? '';
    }

    #[Override]
    public function __toString(): string
    {
        return $this->toString();
    }
}
