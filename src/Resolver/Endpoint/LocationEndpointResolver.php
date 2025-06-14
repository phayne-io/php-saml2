<?php

/**
 * This file is part of phayne-io/php-saml2 and is proprietary and confidential.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @see       https://github.com/phayne-io/php-saml2 for the canonical source repository
 * @copyright Copyright (c) 2024-2025 Phayne Limited. (https://phayne.io)
 */

declare(strict_types=1);

namespace Phayne\Saml\Resolver\Endpoint;

use Override;
use Phayne\Saml\Criteria\CriteriaSet;
use Phayne\Saml\Model\Metadata\EndpointReference;
use Phayne\Saml\Resolver\Endpoint\Criteria\LocationCriteria;

/**
 * Class LocationEndpointResolver
 *
 * @package Phayne\Saml\Resolver\Endpoint
 */
class LocationEndpointResolver implements EndpointResolverInterface
{
    #[Override]
    public function resolve(CriteriaSet $criteriaSet, array $candidates): array
    {
        if (false === $criteriaSet->has(LocationCriteria::class)) {
            return $candidates;
        }

        $result = [];

        /** @var LocationCriteria $criteria */
        foreach ($criteriaSet->get(LocationCriteria::class) as $criteria) {
            /** @var EndpointReference $endpointReference */
            foreach ($candidates as $endpointReference) {
                if ($endpointReference->endpoint->location === $criteria->location) {
                    $result[] = $endpointReference;
                }
            }
        }

        return $result;
    }
}
