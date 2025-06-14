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
use Phayne\Saml\Credential\Criteria\X509CredentialCriteria;
use Phayne\Saml\Criteria\CriteriaSet;

use function array_filter;

/**
 * Class X509CredentialResolver
 *
 * @package Phayne\Saml\Resolver\Credential
 */
class X509CredentialResolver extends AbstractQueryableResolver
{
    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $credentials = []): array
    {
        if (false === $criteriaSet->has(X509CredentialCriteria::class)) {
            return $credentials;
        }

        return array_filter(
            $credentials,
            fn (CredentialInterface $credential) => $credential instanceof X509CredentialCriteria
        );
    }
}
