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

/**
 * Class AbstractQueryableResolver
 *
 * @package Phayne\Saml\Resolver\Credential
 */
abstract class AbstractQueryableResolver implements CredentialResolverInterface
{
    #[Override]
    public function query(): CredentialResolverQuery
    {
        return new CredentialResolverQuery($this);
    }
}
