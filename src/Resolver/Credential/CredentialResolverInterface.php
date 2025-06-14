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

use Phayne\Saml\Credential\CredentialInterface;
use Phayne\Saml\Criteria\CriteriaSet;

/**
 * Interface CredentialResolverInterface
 *
 * @package Phayne\Saml\Resolver\Credential
 */
interface CredentialResolverInterface
{
    /**
     * @param CriteriaSet $criteriaSet
     * @param array $credentials
     * @return CredentialInterface[]
     */
    public function resolve(CriteriaSet $criteriaSet, array $credentials = []): array;

    public function query(): CredentialResolverQuery;
}
