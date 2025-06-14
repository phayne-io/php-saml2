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
use Phayne\Saml\Credential\Criteria\PublicKeyThumbprintCriteria;
use Phayne\Saml\Criteria\CriteriaSet;

/**
 * Class PublicKeyThumbprintResolver
 *
 * @package Phayne\Saml\Resolver\Credential
 */
class PublicKeyThumbprintResolver extends AbstractQueryableResolver
{
    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $credentials = []): array
    {
        if (false === $criteriaSet->has(PublicKeyThumbprintCriteria::class)) {
            return $credentials;
        }

        $result = [];

        /** @var PublicKeyThumbprintCriteria $criteria */
        foreach ($criteriaSet->get(PublicKeyThumbprintCriteria::class) as $criteria) {
            /** @var CredentialInterface $credential */
            foreach ($credentials as $credential) {
                if (
                    null !== $credential->publicKey &&
                    $credential->publicKey->getX509Thumbprint() === $criteria->thumbprint
                ) {
                    $result[] = $credential;
                }
            }
        }

        return $result;
    }
}
