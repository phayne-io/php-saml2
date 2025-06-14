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
use Phayne\Saml\Credential\Criteria\EntityIdCriteria;
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Store\Credential\CredentialStoreInterface;

use function array_merge;

/**
 * Class EntityIdResolver
 *
 * @package Phayne\Saml\Resolver\Credential
 */
class EntityIdResolver extends AbstractQueryableResolver
{
    public function __construct(protected CredentialStoreInterface $credentialStore)
    {
    }

    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $credentials = []): array
    {
        $result = [];

        /** @var EntityIdCriteria $criteria */
        foreach ($criteriaSet->get(EntityIdCriteria::class) as $criteria) {
            $result = array_merge($result, $this->credentialStore->entityById($criteria->entityId));
        }

        return $result;
    }
}
