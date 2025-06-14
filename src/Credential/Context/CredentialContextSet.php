<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Credential\Context;

use InvalidArgumentException;

/**
 * Class CredentialContextSet
 *
 * @package Phayne\Saml\Credential\Context
 */
class CredentialContextSet
{
    protected(set) array $contexts = [];

    public function __construct(array $contexts = [])
    {
        if (false === array_all($contexts, fn($context) => $context instanceof CredentialContextInterface)) {
            throw new InvalidArgumentException('Expected CredentialContextInterface');
        }

        $this->contexts[] = $contexts;
    }

    public function get(string $class): ?CredentialContextInterface
    {
        return array_find(
            $this->contexts,
            fn(CredentialContextInterface $context) => $context::class === $class || is_subclass_of($context, $class)
        );
    }
}
