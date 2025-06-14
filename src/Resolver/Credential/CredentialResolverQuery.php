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
 * Class CredentialResolverQuery
 *
 * @package Phayne\Saml\Resolver\Credential
 */
class CredentialResolverQuery extends CriteriaSet
{
    private(set) array $credentials = [];

    public function __construct(private readonly CredentialResolverInterface $resolver)
    {
        parent::__construct();
    }

    public function resolve(): CredentialResolverQuery
    {
        $this->credentials = $this->resolver->resolve($this);
        return $this;
    }

    public function firstCredential(): ?CredentialInterface
    {
        return reset($this->credentials) ?: null;
    }

    public function publicKeys(): array
    {
        return array_filter(
            $this->credentials,
            fn (CredentialInterface $credential) => null !== $credential->publicKey
        );
    }

    public function privateKeys(): array
    {
        return array_filter(
            $this->credentials,
            fn (CredentialInterface $credential) => null !== $credential->privateKey
        );
    }
}
