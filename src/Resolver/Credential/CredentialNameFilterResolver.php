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
use Phayne\Saml\Credential\Criteria\CredentialNameCriteria;
use Phayne\Saml\Criteria\CriteriaSet;

/**
 * Class CredentialNameFilterResolver
 *
 * @package Phayne\Saml\Resolver\Credential
 */
class CredentialNameFilterResolver extends AbstractQueryableResolver
{
    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $credentials = []): array
    {
        if (false === $criteriaSet->has(CredentialNameCriteria::class)) {
            return $credentials;
        }

        $result = [];

        /** @var CredentialNameCriteria $criteria */
        foreach ($criteriaSet->get(CredentialNameCriteria::class) as $criteria) {
            /** @var CredentialInterface $credential */
            foreach ($credentials as $credential) {
                $credentialNames = $credential->keyNames;

                foreach ($credentialNames as $credentialName) {
                    if ($credentialName === $criteria->name) {
                        $result[] = $credential;
                        break;
                    }
                }
            }
        }

        return $result;
    }
}
