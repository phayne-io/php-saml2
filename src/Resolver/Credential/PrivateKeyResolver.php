<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Resolver\Credential;

use Override;
use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Credential\Criteria\PrivateKeyCriteria;
use Phayne\Saml\Criteria\CriteriaSet;

use function array_filter;

/**
 * Class PrivateKeyResolver
 *
 * @package Phayne\Saml\Resolver\Credential
 */
class PrivateKeyResolver extends AbstractQueryableResolver
{
    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $credentials = []): array
    {
        if (false === $criteriaSet->has(PrivateKeyCriteria::class)) {
            return $credentials;
        }

        return array_filter($credentials, fn (CredentialInterface $credential) => null !== $credential->privateKey);
    }
}
